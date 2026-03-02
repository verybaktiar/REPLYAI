#!/usr/bin/env node

/**
 * Puppeteer-based Web Scraper for KB Import
 * Usage: node puppeteer-scraper.js <url> <output-json-path>
 */

const puppeteer = require('puppeteer');
const fs = require('fs');

async function scrapeWebsite(url, outputPath) {
    let browser;
    
    try {
        console.log(JSON.stringify({ step: 'Membuka browser...', progress: 10 }));
        
        browser = await puppeteer.launch({
            headless: 'new',
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage',
                '--disable-accelerated-2d-canvas',
                '--no-first-run',
                '--no-zygote',
                '--single-process',
                '--disable-gpu',
                '--window-size=1920,1080'
            ],
            timeout: 30000
        });

        const page = await browser.newPage();
        
        // Set stealth user agent
        await page.setUserAgent(
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
        );
        
        // Set viewport
        await page.setViewport({ width: 1920, height: 1080 });
        
        // Additional headers to appear more like a real browser
        await page.setExtraHTTPHeaders({
            'Accept-Language': 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
            'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Encoding': 'gzip, deflate, br',
            'Connection': 'keep-alive',
            'Upgrade-Insecure-Requests': '1'
        });

        console.log(JSON.stringify({ step: 'Memuat website...', progress: 30 }));
        
        // Navigate and wait for network idle
        const response = await page.goto(url, {
            waitUntil: 'networkidle2',
            timeout: 30000
        });

        if (response.status() >= 400) {
            throw new Error(`Website returned status ${response.status()}`);
        }

        console.log(JSON.stringify({ step: 'Mengekstrak konten...', progress: 50 }));

        // Wait a bit for any lazy-loaded content
        await new Promise(r => setTimeout(r, 2000));

        // Extract structured data
        const extractedData = await page.evaluate(() => {
            const data = {
                title: document.title,
                metaDescription: document.querySelector('meta[name="description"]')?.content || '',
                businessName: '',
                tagline: '',
                pricing: [],
                features: [],
                about: '',
                faq: []
            };

            // Try to find business name from common selectors
            const businessSelectors = [
                'header h1', 'nav .brand', '.logo', 'h1', 
                '[class*="brand"]', '[class*="logo"]',
                '.company-name', '.business-name'
            ];
            
            for (const selector of businessSelectors) {
                const el = document.querySelector(selector);
                if (el && el.textContent.trim()) {
                    data.businessName = el.textContent.trim().substring(0, 100);
                    break;
                }
            }

            // Try to find tagline
            const taglineSelectors = [
                'header h2', '.tagline', '.slogan', '[class*="tagline"]',
                '[class*="subtitle"]', 'h2'
            ];
            
            for (const selector of taglineSelectors) {
                const el = document.querySelector(selector);
                if (el && el.textContent.trim() && el.textContent.trim() !== data.businessName) {
                    data.tagline = el.textContent.trim().substring(0, 200);
                    break;
                }
            }

            // Extract pricing information
            const priceKeywords = /(harga|price|mulai dari|starting at|rp\s*[\d.,]+|\$[\d.,]+)/i;
            
            // Look for pricing cards/containers
            const pricingSelectors = [
                '[class*="price"]', '[class*="pricing"]', '[class*="paket"]',
                '[class*="plan"]', '[class*="package"]', '.card',
                '[class*="product-card"]', '[class*="service"]'
            ];

            const processedElements = new Set();

            for (const selector of pricingSelectors) {
                const elements = document.querySelectorAll(selector);
                
                elements.forEach((el, index) => {
                    if (processedElements.has(el) || data.pricing.length >= 5) return;
                    
                    const text = el.innerText || el.textContent;
                    if (priceKeywords.test(text)) {
                        // Extract price
                        const priceMatch = text.match(/(Rp\s*[\d.,]+(?:\.\d{3})*(?:,\d{2})?|\$[\d.,]+(?:\.\d{2})?)/i);
                        
                        // Extract package name
                        const heading = el.querySelector('h1, h2, h3, h4, .title, [class*="name"]');
                        const name = heading ? heading.textContent.trim() : `Paket ${index + 1}`;
                        
                        // Extract features list
                        const features = [];
                        const listItems = el.querySelectorAll('li, [class*="feature"], [class*="item"]');
                        listItems.forEach(li => {
                            const featureText = li.textContent.trim();
                            if (featureText && featureText.length < 100 && !featureText.includes('Rp')) {
                                features.push(featureText);
                            }
                        });

                        // Extract period (bulan/tahun/month/year)
                        const periodMatch = text.match(/\/(bulan|tahun|month|year|mo|yr)/i);
                        const period = periodMatch ? periodMatch[1] : 'bulan';

                        if (priceMatch || features.length > 0) {
                            data.pricing.push({
                                name: name.substring(0, 50),
                                price: priceMatch ? priceMatch[1] : 'Hubungi kami',
                                period: period,
                                features: features.slice(0, 10),
                                rawText: text.substring(0, 500)
                            });
                            processedElements.add(el);
                        }
                    }
                });
            }

            // Extract about/description content
            const aboutSelectors = [
                'section[class*="about"] p', '[class*="about"] p',
                'section[class*="tentang"] p', '[class*="description"]',
                '[class*="overview"]', 'main > p', 'article p'
            ];
            
            for (const selector of aboutSelectors) {
                const paragraphs = document.querySelectorAll(selector);
                let aboutText = '';
                paragraphs.forEach(p => {
                    if (aboutText.length < 500) {
                        aboutText += ' ' + p.textContent.trim();
                    }
                });
                if (aboutText.trim().length > 50) {
                    data.about = aboutText.trim().substring(0, 800);
                    break;
                }
            }

            // Extract FAQ
            const faqSelectors = [
                '[class*="faq"] details', '[class*="faq"] [class*="item"]',
                'details', '[class*="accordion"]'
            ];

            for (const selector of faqSelectors) {
                const items = document.querySelectorAll(selector);
                
                items.forEach((item, index) => {
                    if (data.faq.length >= 10) return;
                    
                    const question = item.querySelector('summary, h3, h4, .question') || item;
                    const answer = item.querySelector('p, .answer, [class*="content"]') || item;
                    
                    const qText = question.textContent.trim();
                    const aText = answer.textContent.trim();
                    
                    if (qText && aText && qText !== aText && qText.length < 200 && aText.length < 500) {
                        data.faq.push({
                            question: qText.substring(0, 200),
                            answer: aText.substring(0, 500)
                        });
                    }
                });
                
                if (data.faq.length > 0) break;
            }

            // Extract general features if no pricing found
            if (data.pricing.length === 0) {
                const featureSelectors = [
                    '[class*="feature"] h3', '[class*="feature"] h4',
                    '[class*="layanan"] h3', '[class*="service"] h3',
                    '.benefits li', '[class*="keunggulan"] li'
                ];

                for (const selector of featureSelectors) {
                    const elements = document.querySelectorAll(selector);
                    
                    elements.forEach(el => {
                        if (data.features.length >= 15) return;
                        const text = el.textContent.trim();
                        if (text && text.length < 100) {
                            data.features.push(text);
                        }
                    });
                    
                    if (data.features.length > 0) break;
                }
            }

            return data;
        });

        console.log(JSON.stringify({ step: 'Memproses hasil...', progress: 80 }));

        // Prepare final result
        const result = {
            success: true,
            url: url,
            extractedAt: new Date().toISOString(),
            data: extractedData,
            entities: {
                hasPricing: extractedData.pricing.length > 0,
                hasFeatures: extractedData.features.length > 0 || extractedData.pricing.some(p => p.features.length > 0),
                hasAbout: extractedData.about.length > 0,
                hasFaq: extractedData.faq.length > 0,
                pricingCount: extractedData.pricing.length,
                faqCount: extractedData.faq.length
            }
        };

        // Save raw HTML for debugging (optional, first 100KB)
        const rawHtml = await page.content();
        result.rawHtml = rawHtml.substring(0, 100000);

        console.log(JSON.stringify({ step: 'Selesai', progress: 100 }));

        // Write output
        fs.writeFileSync(outputPath, JSON.stringify(result, null, 2), 'utf8');
        
        return result;

    } catch (error) {
        const errorResult = {
            success: false,
            url: url,
            error: error.message,
            extractedAt: new Date().toISOString()
        };
        
        fs.writeFileSync(outputPath, JSON.stringify(errorResult, null, 2), 'utf8');
        throw error;
        
    } finally {
        if (browser) {
            await browser.close();
        }
    }
}

// Main execution
const url = process.argv[2];
const outputPath = process.argv[3];

if (!url || !outputPath) {
    console.error('Usage: node puppeteer-scraper.js <url> <output-json-path>');
    process.exit(1);
}

scrapeWebsite(url, outputPath)
    .then(() => process.exit(0))
    .catch(() => process.exit(1));
