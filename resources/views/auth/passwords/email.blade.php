@extends('layouts.app')

@section('content')
<div class="wrapinsidecontent login">
    <div class="box">
        <div class="card">
            <div class="card-header">{{ __('Reset Password') }}</div>
            <div class="card-body">
                @if (session('status'))
                <div class="alert alert-success" role="alert">
                    <span>You will be redirected within the next 5 seconds to the login page or you can click
                        <a href="{{ route('login') }}" class="text-info" style="text-decoration: underline;">
                            here.
                        </a>
                    </span>
                </div>
                @endif
                <form method="POST" action="{{ route('password.email') }}">
                    @csrf
                    <div class="form-group row">
                        <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('E-Mail Address') }}</label>
                        <div class="col-md-6">
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                            @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>
                    <div class="form-group row mb-0">
                        <div class="col-md-6 offset-md-4">
                            <button type="submit" class="btn btn-primary">
                                {{ __('Send Password Reset Link') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        if ("{{ session('status') }}") {
            setTimeout(() => {
                window.location.href = "{{ route('login') }}";
            }, 5000);
        }
    });
</script>
@endsection