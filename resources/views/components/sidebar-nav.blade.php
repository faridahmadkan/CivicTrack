@props(['active' => '', 'items' => [], 'class' => '', 'attributes' => []])

<nav class="sidebar-nav {{ $class }}" {{ $attributes }} aria-label="Sidebar navigation">
    @foreach($items as $item)
        <a href="{{ route($item['route']) }}"
           class="sidebar-nav-item {{ $active === $item['route'] || request()->routeIs($item['route'].'*') ? 'active' : '' }}"
           aria-current="{{ $active === $item['route'] || request()->routeIs($item['route'].'*') ? 'page' : 'false' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"></path></svg>
            <span>{{ $item['label'] }}</span>
        </a>
    @endforeach
</nav>