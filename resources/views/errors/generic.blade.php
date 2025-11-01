@extends('mcp-builder::layouts.app')

@section('title', 'Error')

@section('content')
<div class="container">
    <div class="row justify-content-center align-items-center" style="min-height: 60vh;">
        <div class="col-md-6">
            @if(isset($exception))
                <x-mcp-builder::error-display 
                    :error="$errorInfo ?? ['message' => $exception->getMessage()]"
                    :showDetails="config('app.debug')"
                />
            @else
                <div class="card border-danger">
                    <div class="card-header bg-danger text-white">
                        <strong>❌ An Error Occurred</strong>
                    </div>
                    <div class="card-body">
                        <p>An unexpected error occurred. Please try again or contact support.</p>
                        <div class="mt-3">
                            <a href="{{ route('mcp-builder.dashboard') }}" class="btn btn-primary">
                                ← Go to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

