@extends('layouts.app')
@section('content')

<div class="indexContent" data-page_name="ADMIN USERS">
	<div class="maincontent">
		<div class="wrapinsidecontent">	

			


					<div class="new-user">
						<h4>Create a new user.</h4>
						<div class="nuform">
							<div>
								<label>User Name: </label> <input type="text" id="txt-user-name">
							</div>
							<div>
								<label>Email: </label> <input type="text" id="txt-email">
							</div>
							<div>
								<label>Password: </label> <input type="text" id="txt-password">
							</div>
							<div>
								<label>Repeat Password: </label> <input type="text" id="txt-repeat-password">
							</div>
							<div>
								<button class="bgVC colorBL" id="btn-save-user">Save</button>
							</div>
						</div>
					</div>


					<div class="orders">
		                <table class="greentable tableorders" cellspacing="0">
		                       <thead>
		                           <tr>
		                               <th>
		                                   <input type="checkbox">
		                               </th>
		                               <th>
		                                   ID
		                               </th>
		                               <th>
		                                   USER NAME
		                               </th>
		                               <th>
		                                   EMAIL
		                               </th>
		                               <th>
		                                   ACTIVE
		                               </th>
		                               <th>
		                                   ACTIONS
		                               </th>
		                           </tr>
		                       </thead>
		                       <tbody>

		                        @php $k = 0 @endphp
						        @foreach($merchants_list as $ml)
							        @if($k == 0)
							         @php 
							            $back = 'transparent';
							            $k = 1;
							         @endphp
							        @else
							         @php 
							            $back = '';
							            $k = 0;
							         @endphp

							        @endif
		                           <tr class="productdatarow">
		                               <td class="check">
		                                   <input type="checkbox">
		                               </td>
		                               <td data-label="ID">
		                                   {{$ml->id}}
		                               </td>
		                               <td data-label="USER NAME">
		                                   {{$ml->name}}
		                               </td>
		                               <td data-label="EMAIL">
		                                  {{$ml->email}}
		                               </td>
		                               <td data-label="ACTIVE">
		                                  <input type="checkbox" name="switch-button" id="switch-label{{$ml->id}}" class="switch-button__checkbox" @if($ml->active == 1)checked @endif>
		                               </td>
		                               <td>
		                                    <button class="view">View</button>
		                               </td>
		                           </tr>
		                        @endforeach

		                       </tbody>
		                </table>
		            </div>
			

		</div>
	</div>
</div>



@endsection