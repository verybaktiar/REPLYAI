import React from 'react';
import { 
  MessageSquare, 
  Bot, 
  Clock, 
  BookOpen, 
  BarChart3, 
  Plus, 
  ArrowUpRight, 
  Sparkles, 
  Megaphone, 
  Building2,
  CheckCircle2,
  Circle
} from 'lucide-react';
import { Link, usePage } from '@inertiajs/react';

// --- Types ---
interface StatCardProps {
  icon: React.ElementType;
  value: number;
  label: string;
  trend: number;
  color: 'blue' | 'violet' | 'orange' | 'emerald';
  ctaText: string;
  ctaLink: string;
}

// --- Components ---

const StatCard: React.FC<StatCardProps> = ({ icon: Icon, value, label, trend, color, ctaText, ctaLink }) => {
  const colorMap = {
    blue: 'text-blue-400 bg-blue-500/15',
    violet: 'text-violet-400 bg-violet-500/15',
    orange: 'text-orange-400 bg-orange-500/15',
    emerald: 'text-emerald-400 bg-emerald-500/15',
  };

  const trendColorMap = {
    blue: 'text-blue-400 bg-blue-500/10',
    violet: 'text-violet-400 bg-violet-500/10',
    orange: 'text-orange-400 bg-orange-500/10',
    emerald: 'text-emerald-400 bg-emerald-500/10',
  };

  const glowColorMap = {
    blue: 'bg-blue-500/10 group-hover:bg-blue-500/20',
    violet: 'bg-violet-500/10 group-hover:bg-violet-500/20',
    orange: 'bg-orange-500/10 group-hover:bg-orange-500/20',
    emerald: 'bg-emerald-500/10 group-hover:bg-emerald-500/20',
  };

  return (
    <div className="bg-gray-900 border border-gray-800 rounded-xl p-6 relative overflow-hidden group hover:border-gray-700 transition-colors">
      <div className={`absolute top-0 right-0 w-32 h-32 rounded-full blur-3xl -mr-10 -mt-10 transition-all ${glowColorMap[color]}`}></div>
      
      <div className="relative z-10">
        <div className="flex items-center justify-between mb-4">
          <div className={`w-12 h-12 rounded-lg flex items-center justify-center ${colorMap[color]}`}>
            <Icon size={24} />
          </div>
          <span className={`text-xs font-medium px-2 py-1 rounded-full ${trendColorMap[color]}`}>+{trend}%</span>
        </div>
        
        <div className="space-y-1">
          <h3 className="text-3xl font-bold text-white tracking-tight">
            {value > 0 ? value.toLocaleString('id-ID') : "Belum ada"}
          </h3>
          <p className="text-sm text-gray-400 font-medium">{label}</p>
        </div>
        
        {value === 0 && (
          <Link href={ctaLink} className={`inline-flex items-center gap-1 mt-4 text-sm font-medium transition-colors ${color === 'blue' ? 'text-blue-400 hover:text-blue-300' : 
                                            color === 'violet' ? 'text-violet-400 hover:text-violet-300' :
                                            color === 'orange' ? 'text-orange-400 hover:text-orange-300' :
                                            'text-emerald-400 hover:text-emerald-300'}`}>
            {ctaText} <ArrowUpRight size={16} />
          </Link>
        )}

        {label === "Menunggu Balasan" && value > 0 && (
            <div className="mt-4">
                 <span className="text-[10px] font-bold bg-red-500/20 text-red-500 px-2 py-1 rounded border border-red-500/20">URGENT</span>
            </div>
        )}
      </div>
    </div>
  );
};

const QuickAction: React.FC<{ icon: React.ElementType; label: string; color: string; href: string }> = ({ icon: Icon, label, color, href }) => (
  <Link href={href} className="flex flex-col items-center justify-center p-4 bg-gray-900 border border-gray-800 rounded-xl hover:bg-gray-800 hover:border-gray-700 transition-all group">
    <div className={`w-10 h-10 rounded-lg bg-gray-800 flex items-center justify-center mb-2 transition-colors ${color}`}>
      <Icon className="text-gray-400 transition-colors" size={20} />
    </div>
    <span className="text-sm font-medium text-gray-300 group-hover:text-white">{label}</span>
  </Link>
);

export default function Dashboard() {
  const { auth, stats, messages_last_7_days, activities } = usePage().props as any;
  const user = auth.user;

  const hasData = messages_last_7_days?.length > 0 && messages_last_7_days.some((d: any) => d.value > 0);

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
      
      {/* SECTION 1: ONBOARDING CHECKLIST */}
      {user.setup_progress < 100 && (
        <div className="bg-indigo-950/30 border border-indigo-800/50 rounded-2xl p-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 mb-6">
          <div className="flex-1">
            <h2 className="text-2xl font-bold text-white">Langkah Awal (1/5)</h2>
            <p className="text-indigo-200 text-sm mt-1">Lengkapi setup untuk mengaktifkan chatbot AI Anda</p>
            
            <div className="mt-4 space-y-2">
              <div className="flex items-center gap-2 text-gray-400 line-through opacity-60">
                <CheckCircle2 size={16} /> <span>Daftar Akun</span>
              </div>
              <div className="bg-indigo-500/20 border border-indigo-500 rounded px-3 py-2 flex items-center gap-2 text-indigo-300">
                 <Circle size={16} /> <span>Hubungkan WhatsApp</span>
              </div>
              <div className="flex items-center gap-2 text-gray-500 opacity-40">
                <Circle size={16} /> <span>Atur Knowledge Base</span>
              </div>
              <div className="flex items-center gap-2 text-gray-500 opacity-40">
                <Circle size={16} /> <span>Test Chat</span>
              </div>
              <div className="flex items-center gap-2 text-gray-500 opacity-40">
                <Circle size={16} /> <span>Aktifkan AI</span>
              </div>
            </div>
          </div>
          
          <div className="flex flex-col items-center lg:items-end gap-3">
            <button className="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-bold transition-all h-12">
               Hubungkan WhatsApp
            </button>
            <button className="text-xs text-gray-500 hover:text-gray-300">Lewati untuk sekarang</button>
          </div>
        </div>
      )}

      {/* SECTION 2: STATS OVERVIEW */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <StatCard 
          icon={MessageSquare} 
          value={stats.total_messages} 
          label="Total Pesan Masuk" 
          trend={12} 
          color="blue"
          ctaText="Hubungkan WhatsApp"
          ctaLink="/integration/whatsapp"
        />
        <StatCard 
          icon={Bot} 
          value={stats.ai_responses} 
          label="Direspon AI" 
          trend={8} 
          color="violet"
          ctaText="Tambah Knowledge Base"
          ctaLink="/knowledge-base/create"
        />
        <StatCard 
          icon={Clock} 
          value={stats.pending_replies} 
          label="Menunggu Balasan" 
          trend={15} 
          color="orange"
          ctaText="Lihat Inbox"
          ctaLink="/chat"
        />
        <StatCard 
          icon={BookOpen} 
          value={stats.kb_articles} 
          label="Artikel Pengetahuan" 
          trend={5} 
          color="emerald"
          ctaText="Tambah Artikel"
          ctaLink="/knowledge-base"
        />
      </div>

      {/* SECTION 3: MAIN DASHBOARD GRID */}
      <div className="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        {/* Left Column: ANALYTICS CHART */}
        <div className="lg:col-span-8 bg-gray-900 border border-gray-800 rounded-xl p-6">
          <div className="flex justify-between items-center mb-6">
            <h3 className="text-lg font-semibold text-white">Volume Interaksi</h3>
            <span className="text-[10px] font-bold text-gray-500 border border-gray-800 px-2 py-1 rounded uppercase tracking-wider">
              7 Hari Terakhir
            </span>
          </div>

          <div className="h-[320px] relative">
            {!hasData ? (
              <div className="flex flex-col items-center justify-center h-full py-12 text-center">
                <div className="w-20 h-20 rounded-full bg-gray-800 flex items-center justify-center mb-6">
                  <BarChart3 className="text-gray-600" size={32} />
                </div>
                <h3 className="text-lg font-semibold text-gray-300 mb-2">Belum ada percakapan</h3>
                <p className="text-sm text-gray-500 max-w-sm mb-6">
                  Dashboard akan menampilkan statistik setelah Anda mulai menerima pesan dari pelanggan.
                </p>
                <button className="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-bold transition-all">
                  <Plus size={18} /> Hubungkan WhatsApp Sekarang
                </button>
              </div>
            ) : (
              <div className="w-full h-full flex items-center justify-center text-gray-500 italic">
                {/* LineChart Implementation goes here */}
                Chart Rendering...
              </div>
            )}
          </div>
        </div>

        {/* Right Column: QUICK ACTIONS + RECENT ACTIVITY */}
        <div className="lg:col-span-4 space-y-6">
          
          {/* Sub-section A: Quick Actions */}
          <div>
            <h3 className="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-4">Aksi Cepat</h3>
            <div className="grid grid-cols-2 gap-3">
              <QuickAction icon={MessageSquare} label="Buka Chat" color="group-hover:bg-blue-500/20" href="/chat" />
              <QuickAction icon={Sparkles} label="Setup AI" color="group-hover:bg-violet-500/20" href="/ai-setup" />
              <QuickAction icon={Megaphone} label="Kirim Promo" color="group-hover:bg-orange-500/20" href="/broadcast" />
              <QuickAction icon={Building2} label="Profil Bisnis" color="group-hover:bg-gray-500/20" href="/settings" />
            </div>
          </div>

          {/* Sub-section B: Recent Activity */}
          <div className="bg-gray-900 border border-gray-800 rounded-xl p-6">
            <h3 className="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-4">Aktivitas Terakhir</h3>
            <div className="space-y-4">
              {activities?.length > 0 ? activities.map((activity: any, i: number) => (
                 <div key={i} className="flex gap-3 items-start">
                    <div className="w-2 h-2 rounded-full bg-blue-500 mt-1.5 shrink-0" />
                    <div>
                        <p className="text-sm text-white font-medium">{activity.text}</p>
                        <p className="text-[10px] text-gray-500 mt-0.5">{activity.time}</p>
                    </div>
                 </div>
              )) : (
                <p className="text-sm text-gray-500 italic">Belum ada aktivitas</p>
              )}
            </div>
          </div>

        </div>

      </div>

    </div>
  );
}
