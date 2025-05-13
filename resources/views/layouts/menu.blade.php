<nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
        <a class="navbar-brand" href="./">Web Security</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
                aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item">
                <a class="nav-link" href="./">Home</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="./even">Even Numbers</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="./prime">Prime Numbers</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="./multable">Multiplication Table</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{route('products_list')}}">Products</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{route('cryptography')}}">Cryptography</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{route('webcrypto')}}">Web Crypto</a>
            </li>
            @role('Customer')
            <li class="nav-item">
                <a class="nav-link" href="{{route('my_purchases')}}">My Purchases</a>
            </li>
            @endrole
            @can('show_users')
            <li class="nav-item">
                <a class="nav-link" href="{{route('users')}}">Users</a>
            </li>
            @endcan
        </ul>

            <ul class="navbar-nav align-items-center">
                <!-- Dark Mode Toggle -->
                <li class="nav-item me-3">
                    <div class="d-flex align-items-center">
                        <label class="theme-switch mb-0" for="darkModeToggle">
                            <input type="checkbox" id="darkModeToggle">
                            <span class="theme-slider"></span>
                        </label>
                        <span class="ms-2 theme-icon" id="themeIcon">
                            <i class="bi bi-sun-fill"></i>
                        </span>
                    </div>
                </li>
                
            @auth
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                       data-bs-toggle="dropdown" aria-expanded="false">
                        {{auth()->user()->name}}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="{{route('profile')}}">Profile</a></li>
            @role('Customer')
                        <li><a class="dropdown-item" href="#">Credit: <span class="badge bg-success">{{auth()->user()->credit}}</span></a></li>
            @endrole
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="{{route('do_logout')}}">Logout</a></li>
                    </ul>
            </li>
            @else
            <li class="nav-item">
                <a class="nav-link" href="{{route('login')}}">Login</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{route('register')}}">Register</a>
            </li>
            @endauth
        </ul>
        </div>
    </div>
</nav>
