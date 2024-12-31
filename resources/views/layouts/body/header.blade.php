<header class="navbar navbar-expand-md d-print-none" >
    <div class="container-xl">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu" aria-controls="navbar-menu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <h1 class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3">
            <a href="{{ url('/') }}">
                <img src="{{ asset('static/logo.svg') }}" width="110" height="32" alt="Tabler" class="navbar-brand-image">
            </a>
        </h1>

        <div class="navbar-nav ms-auto d-flex align-items-center">
            <!-- Notification Icon with Count -->
            <div class="nav-item dropdown me-3 position-relative">
                <a class="nav-link" href="{{ route('notifications.index') }}" aria-label="Notifications">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 48 48">
                        <path fill="#DB8509" d="M24 34.001A4 4 0 1 0 24 42.001 4 4 0 1 0 24 34.001zM26 11c0 1.104-.896 2-2 2l0 0c-1.104 0-2-.896-2-2V7c0-1.104.896-2 2-2l0 0c1.104 0 2 .896 2 2V11z"></path>
                        <path fill="#BC6F0A" d="M27.887,38.91C27.955,38.617,28,38.314,28,38.001c0-0.383-0.07-0.746-0.172-1.097C26.609,36.965,25.333,37,24,37s-2.609-0.035-3.829-0.096C20.071,37.255,20,37.618,20,38.001c0,0.313,0.045,0.616,0.113,0.909C21.584,38.979,22.926,39,24,39S26.416,38.979,27.887,38.91z"></path>
                        <path fill="#FFC107" d="M33,33V16.801c0-4.97-4.029-9-9-9s-9,4.03-9,9V33H33z"></path>
                        <path fill="#FFC107" d="M41,33c0,2.209-7.059,4-17,4S7,35.209,7,33s7.059-4,17-4S41,30.791,41,33z"></path>
                        <path fill="#FFC107" d="M7,33c0-1.999,8-9.001,8-12s18-3.001,18,0s8,9.999,8,12S7,34.999,7,33z"></path>
                        <path fill="#FFE082" d="M9.275,29.424c-0.491,0.593-0.929,1.146-1.295,1.65c1.622,0.655,8.15,3.062,16.562,3.062c4.746,0,10.089-0.78,15.476-3.064c-0.362-0.499-0.796-1.046-1.281-1.632C25.699,34.724,12.698,30.68,9.275,29.424z"></path>
                    </svg>
                    <span class="badge bg-info position-absolute" 
                        style="top: 15px; right:16px; font-size: 0.75rem; padding: 0.2rem 0.4rem; border-radius: 50%;">
                        {{ \App\Models\StockNotification::where('read', false)->count() }}
                    </span>
                </a>
            </div>

            <!-- User Profile Dropdown -->
            <div class="nav-item dropdown">
                <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Open user menu">
                    <span class="avatar avatar-sm shadow-none"
                        style="background-image: url({{ Avatar::create(Auth::user()->name)->toBase64() }})">
                    </span>
                    <div class="d-none d-xl-block ps-2">
                        <div>{{ Auth::user()->name }}</div>
                    </div>
                </a>

                <div class="dropdown-menu">
                    <a href="{{ route('profile.edit') }}" class="dropdown-item">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon icon-tabler icon-tabler-settings" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z"></path>
                            <path d="M12 12m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"></path>
                        </svg>
                        Account
                    </a>
                    <form action="{{ route('logout') }}" method="post">
                        @csrf
                        <button type="submit" class="dropdown-item">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon icon-tabler icon-tabler-logout" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2"/>
                                <path d="M9 12h12l-3 -3"/>
                                <path d="M18 15l3 -3"/>
                            </svg>
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
