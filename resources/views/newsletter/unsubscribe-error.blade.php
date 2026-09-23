@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-danger">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <svg class="text-danger" style="width: 60px; height: 60px;" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <h2 class="card-title mb-3">Unable to Unsubscribe</h2>
                    <p class="card-text text-muted mb-4">
                        {{ $message ?? 'We could not process your unsubscribe request.' }}
                    </p>
                    <p class="card-text text-muted small mb-4">
                        Please make sure you're using the correct unsubscribe link from our email.
                    </p>
                    <a href="{{ route('home') }}" class="btn btn-primary">Return to Home</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
