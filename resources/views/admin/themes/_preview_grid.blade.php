<svg viewBox="0 0 320 160" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
  <rect width="320" height="160" fill="#f9fafb"/>
  <!-- header -->
  <rect width="320" height="20" fill="#ffffff" stroke="#e5e7eb" stroke-width=".5"/>
  <rect x="10" y="7" width="50" height="7" rx="2" fill="#374151"/>
  <!-- masonry grid -->
  @php
  $cards = [
    [8,26,94,78,'#e2e8f0'],
    [110,26,94,50,'#dbeafe'],
    [212,26,100,50,'#fce7f3'],
    [110,82,94,40,'#d1fae5'],
    [212,82,100,40,'#fef3c7'],
    [8,110,94,44,'#ede9fe'],
    [110,128,94,26,'#fee2e2'],
    [212,128,100,26,'#f0fdf4'],
  ];
  @endphp
  @foreach($cards as [$x,$y,$w,$h,$fill])
  <rect x="{{ $x }}" y="{{ $y }}" width="{{ $w }}" height="{{ $h }}" rx="4" fill="{{ $fill }}"/>
  <rect x="{{ $x+6 }}" y="{{ $y+$h-18 }}" width="{{ $w-12 }}" height="5" rx="1" fill="#374151" opacity=".7"/>
  <rect x="{{ $x+6 }}" y="{{ $y+$h-10 }}" width="{{ $w-20 }}" height="3" rx="1" fill="#6b7280" opacity=".5"/>
  @endforeach
</svg>
