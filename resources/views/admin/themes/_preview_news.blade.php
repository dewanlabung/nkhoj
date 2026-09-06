<svg viewBox="0 0 320 160" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
  <rect width="320" height="160" fill="#ffffff"/>
  <!-- top bar -->
  <rect width="320" height="14" fill="#1e293b"/>
  <rect x="8" y="4" width="40" height="6" rx="1" fill="#fff" opacity=".7"/>
  <!-- nav strip -->
  <rect y="14" width="320" height="18" fill="#0f172a"/>
  <rect x="8" y="19" width="30" height="6" rx="1" fill="#fff" opacity=".5"/>
  <rect x="46" y="19" width="30" height="6" rx="1" fill="#fff" opacity=".5"/>
  <rect x="84" y="19" width="30" height="6" rx="1" fill="#fff" opacity=".5"/>
  <rect x="122" y="19" width="30" height="6" rx="1" fill="#fff" opacity=".5"/>
  <!-- breaking ticker -->
  <rect y="32" width="320" height="10" fill="#ef4444"/>
  <rect x="8" y="34.5" width="50" height="5" rx="1" fill="#fff" opacity=".9"/>
  <rect x="66" y="34.5" width="180" height="5" rx="1" fill="#fff" opacity=".6"/>
  <!-- grid headlines -->
  @php $cols = [[8,48,130,70],[146,48,130,70],[8,125,94,28],[110,125,94,28],[212,125,100,28]]; @endphp
  @foreach($cols as [$x,$y,$w,$h])
  <rect x="{{ $x }}" y="{{ $y }}" width="{{ $w }}" height="{{ $h }}" rx="3" fill="#f1f5f9"/>
  <rect x="{{ $x+6 }}" y="{{ $y+8 }}" width="{{ min($w-12,70) }}" height="5" rx="1" fill="#334155" opacity=".8"/>
  <rect x="{{ $x+6 }}" y="{{ $y+16 }}" width="{{ min($w-12,55) }}" height="4" rx="1" fill="#64748b" opacity=".6"/>
  @endforeach
</svg>
