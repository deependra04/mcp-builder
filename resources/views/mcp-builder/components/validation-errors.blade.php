@props(['errors'])

@if($errors->any())
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <h5 class="alert-heading">⚠️ Please correct the following errors:</h5>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

