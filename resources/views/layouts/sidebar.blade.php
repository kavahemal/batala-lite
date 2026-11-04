<nav id="sidebar" class="sidebar js-sidebar">
    <div class="sidebar-content js-simplebar">
        <a class="sidebar-brand text-decoration-none" href="{{ route('home') }}">
            <img src="{!! asset('assets/img/batala.png') !!}" class="img-fluid me-1" alt="{{ Auth::user()->name }}" height="24" width="24" />
            <span class="align-middle">{{ config('app.name', 'Laravel') }}</span>
        </a>

        <ul class="sidebar-nav">
            <li class="sidebar-header"></li>

            <li class="sidebar-item {{ Request::is('home*') ? 'active' : '' }}">
                <a class="sidebar-link" href="{{ route('home') }}">
                    <i class="align-middle" data-feather="sliders"></i> <span class="align-middle">Dashboard</span>
                </a>
            </li>

            @php
            // Locked (full version) items are shown to everyone; the others need their view permission
            $menus = config('permissions');
            $visible = fn ($menu) => ($menu['in_menu'] ?? true) && (($menu['locked'] ?? false) || (isset($menu['action']) && auth()->user()->can($menu['action'])));
            @endphp
            @foreach($menus as $resource => $menu)
            @if(isset($menu['submenus']))
            @if(collect($menu['submenus'])->contains(fn ($submenu) => isset($menus[$submenu]) && $visible($menus[$submenu])))
            <li class="sidebar-header">{{ $menu['title'] }}</li>
            @endif
            @elseif($visible($menu))
            @if($menu['locked'] ?? false)
            <li class="sidebar-item {{ Request::is('full-version/' . $resource) ? 'active' : '' }}">
                <a class="sidebar-link" href="{{ route('preview', $resource) }}" title="Part of the full version">
                    <i class="align-middle" data-feather="{{ $menu['icon'] }}"></i> <span class="align-middle">{{ $menu['title'] }}</span>
                    <span class="sidebar-badge" style="line-height: 1;"><i data-feather="lock" class="me-0 text-info" style="width: 14px; height: 14px;"></i></span>
                </a>
            </li>
            @else
            <li class="sidebar-item {{ Request::is($resource . '*') ? 'active' : '' }}">
                <a class="sidebar-link" href="{{ route($menu['action']) }}">
                    <i class="align-middle" data-feather="{{ $menu['icon'] }}"></i> <span class="align-middle">{{ $menu['title'] }}</span>
                </a>
            </li>
            @endif
            @endif
            @endforeach

            @role('admin|owner')
            @cannot('users.index')
            <li class="sidebar-header">Settings</li>
            @endcannot
            <li class="sidebar-item {{ Request::is('role-permissions*') ? 'active' : '' }}">
                <a class="sidebar-link" href="{{ route('role-permissions.index') }}">
                    <i class="align-middle" data-feather="settings"></i> <span class="align-middle">Permissions</span>
                </a>
            </li>
            @endrole
        </ul>
    </div>
</nav>