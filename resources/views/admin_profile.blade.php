@extends('layouts.app')
@section('content')

<div class="indexContent" data-page_name="ADMIN USERS">
	<div class="maincontent">
		<div class="wrapinsidecontent">
			<div class="profile">
				<h4>Edit profile</h4>
				<h5 style="display: none;" id="success-profile">Updated successfully.</h5>
				<h5 style="display: none;" id="fail-user">The email already exists.</h5>
				<div class="nuform">
					<div class="user-info">
						<label>User Name: </label><input type="text" minlength="3" value="{{ $user->name }}" id="txt-user-name" required>
						<p style="display: none;" id="name-error"></p>
					</div>
					<div class="user-info">
						<label>Email: </label><input type="email" value="{{ $user->email }}" id="txt-email" pattern="([A-Za-z0-9][._]?)+[A-Za-z0-9]@[A-Za-z0-9]+(\.?[A-Za-z0-9]){2}\.(com?|net|org)+(\.[A-Za-z0-9]{2,4})?" autocomplete="email" required>
						<p style="display: none;" id="email-error"></p>
					</div>
					<div class="user-info">
						<label>Password: </label><input type="password" minlength="12" id="txt-password" title="more than 12 characters, lowercase, uppercase, number, symbol" autocomplete="new-password" required><i class="fa fa-eye" data-id="#txt-password"></i>
						<p id="password-error"></p>
					</div>
					<div class="user-info">
						<label>Confirm Password: </label><input type="password" minlength="12" id="txt-confirm-password" autocomplete="new-password" required><i class="fa fa-eye" data-id="#txt-confirm-password"></i>
						<p style="display: none;" id="confirm-error"></p>
					</div>
					<div class="update-user">
						<button class="bgVC colorBL btn btn-success" id="btn-save-user">Save</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

@endsection