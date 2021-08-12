@extends('layouts.app')
@section('content')

<div class="indexContent" data-page_name="ADMIN USERS">
	<div class="maincontent">
		<div class="wrapinsidecontent">
			<div class="profile">
				<h4>Change Password</h4>
				<h5 style="display: none;" id="success-password">Updated successfully.</h5>
				<h5 style="display: none;" id="fail-password">The current password doesn't match.</h5>
				<div class="nuform">
					<div class="user-info">
						<label>Current Password: </label><input type="password" minlength="12" id="old-password" autocomplete="new-password" required><i class="fa fa-eye" data-id="#old-password"></i>
						<p style="display: none;" id="old-error"></p>
					</div>
					<div class="user-info">
						<label>New Password: </label><input type="password" minlength="12" id="new-password" title="more than 12 characters, lowercase, uppercase, number, symbol" autocomplete="new-password" required><i class="fa fa-eye" data-id="#new-password"></i>
						<p id="password-error"></p>
					</div>
					<div class="user-info">
						<label>Confirm Password: </label><input type="password" minlength="12" id="confirm-password" autocomplete="new-password" required><i class="fa fa-eye" data-id="#confirm-password"></i>
						<p style="display: none;" id="confirm-error"></p>
					</div>
					<div class="update-user">
						<button class="bgVC colorBL" id="btn-save-password">Save</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection