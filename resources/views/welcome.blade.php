@extends('layouts.master')
@section('title', 'Secure Shop - Black1hp')
@section('content')
<div class="container py-5">
    <!-- Hero Section with Hacker Theme -->
    <div class="card bg-dark text-light mb-5 border-success">
        <div class="card-body p-5">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-4"><span class="text-success">BLACK<span class="text-danger">1</span>HP</span> <span class="text-muted">Secure Shop</span></h1>
                    <p class="lead mb-4">Welcome to the most secure e-commerce platform developed by <span class="text-success">Mohamed Saied</span>. Shop with confidence knowing your data is protected by military-grade encryption.</p>
                    <div class="d-flex gap-3">
                        <a href="{{ route('products_list') }}" class="btn btn-success px-4 py-2">
                            <i class="bi bi-shield-lock me-2"></i>Browse Products
                        </a>
                        @guest
                        <a href="{{ route('login') }}" class="btn btn-outline-light px-4 py-2">
                            <i class="bi bi-person-lock me-2"></i>Secure Login
                        </a>
                        @endguest
                    </div>
                </div>
                <div class="col-lg-4 d-none d-lg-block text-center">
                    <!-- ASCII Art Shield or Lock -->
                    <pre class="text-success" style="font-size: 0.5rem; line-height: 0.7rem;">
      .-------.
     / .-----. \
    / /  ---  \ \
   | |  BLACK  | |
   | |  1HP    | |
   \ \  ---  / /
    \ '-----' /
     '-------'
                    </pre>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Cards -->
    <div class="row mb-5">
        <div class="col-md-4 mb-4">
            <div class="card h-100 border-success bg-dark text-light">
                <div class="card-body text-center">
                    <div class="display-1 text-success mb-3">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h3 class="card-title">Secure Transactions</h3>
                    <p class="card-text">All transactions are encrypted and protected. Our return system is thoroughly tested against SQL injection and XSS.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100 border-danger bg-dark text-light">
                <div class="card-body text-center">
                    <div class="display-1 text-danger mb-3">
                        <i class="bi bi-currency-exchange"></i>
                    </div>
                    <h3 class="card-title">Safe Returns</h3>
                    <p class="card-text">Our secure product return system guarantees your credit is returned without exploits. Tested by Black1hp himself!</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100 border-warning bg-dark text-light">
                <div class="card-body text-center">
                    <div class="display-1 text-warning mb-3">
                        <i class="bi bi-patch-check"></i>
                    </div>
                    <h3 class="card-title">Pentested Platform</h3>
                    <p class="card-text">Our e-commerce platform has been rigorously tested by security professionals to protect against common vulnerabilities.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Console-like Terminal Section -->
    <div class="card bg-black text-light border-success mb-5">
        <div class="card-header bg-success text-dark d-flex justify-content-between align-items-center">
            <span><i class="bi bi-terminal me-2"></i>Black1hp@SecureShop:~#</span>
            <div>
                <span class="badge bg-danger rounded-circle p-1">×</span>
                <span class="badge bg-warning rounded-circle p-1">−</span>
                <span class="badge bg-success rounded-circle p-1">+</span>
            </div>
        </div>
        <div class="card-body p-3" style="font-family: monospace;">
            <div class="mb-2">
                <span class="text-success">$</span> <span class="text-light">./check_security.sh</span>
            </div>
            <div class="mb-2">
                <span class="text-success">[ OK ]</span> SQL Injection Protection
            </div>
            <div class="mb-2">
                <span class="text-success">[ OK ]</span> XSS Protection
            </div>
            <div class="mb-2">
                <span class="text-success">[ OK ]</span> CSRF Protection
            </div>
            <div class="mb-2">
                <span class="text-success">[ OK ]</span> Return System Validation
            </div>
            <div>
                <span class="text-success">$</span> <span class="text-light">echo "System is secure and ready!"</span>
            </div>
            <div>
                <span class="text-warning">System is secure and ready!</span>
            </div>
        </div>
    </div>
</div>

<!-- Add Bootstrap Icons to the layout -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endsection
