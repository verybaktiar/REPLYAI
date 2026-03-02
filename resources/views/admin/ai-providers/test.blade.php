@extends('admin.layouts.app')

@section('title', 'AI Provider Test')

@section('content')
<div class="p-6">
    <h1 class="text-xl font-bold text-white mb-4">Test Buttons</h1>
    
    <button type="button" onclick="alert('Button clicked!')" 
            class="px-4 py-2 bg-blue-500 text-white rounded cursor-pointer">
        Test Click
    </button>
    
    <button type="button" onclick="switchTest()" 
            class="px-4 py-2 bg-green-500 text-white rounded cursor-pointer ml-4">
        Switch Test
    </button>
</div>

<script>
function switchTest() {
    alert('Switch function works!');
}
</script>
@endsection
