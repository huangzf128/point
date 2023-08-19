
<!--Navigation bar start-->
<nav class="navbar fixed-top navbar-expand-sm navbar-dark" style="background-color:rgba(0,0,0,0.9)">
	<div class="container-fluid">
			<a href="index" class="navbar-brand" style="font-family: 'Delius Swash Caps'">B-POINTNOUKI</a>
			<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mynavbar">
				<span class="navbar-toggler-icon"></span>
			</button>
		<div class="collapse navbar-collapse" id="mynavbar">
			<ul class="nav navbar-nav">
				<li class="nav-item dropdown">
					<a href="#" class="nav-link dropdown-toggle" id="navbarDropdown" data-bs-toggle="dropdown">
						商品種別から探す
					</a>
						<div class="dropdown-menu" aria-labelledby="navbarDropdown">
						{% for category in categorys %}
							<a href="product?id={{ category.id }}" class="dropdown-item">{{ category.name }}</a>
						{% endfor %}
						</div>
				</li>
				<!-- <li class="nav-item"><a href="index.php" class="nav-link">Offers</a></li> -->
				<li class="nav-item"><a href="about" class="nav-link">关于B-POINT</a></li>
				<li class="nav-item dropdown">
					<a href="#" class="nav-link dropdown-toggle" id="navbarDropdownMaint" data-bs-toggle="dropdown">
						配送・送料
					</a>
					<div class="dropdown-menu" aria-labelledby="navbarDropdownMaint">
						<a href="ship" class="dropdown-item">通常配送</a>
						<a href="charter-ship" class="dropdown-item">チャータ―便</a>
					</div>
				</li>

				{% if type == 9 %}
					<li class="nav-item dropdown">
						<a href="#" class="nav-link dropdown-toggle" id="navbarDropdownMaint" data-bs-toggle="dropdown">
							管理
						</a>
						<div class="dropdown-menu" aria-labelledby="navbarDropdownMaint">
							<a href="maint" class="dropdown-item">商品管理</a>
							<a href="maint-user" class="dropdown-item">ユーザー管理</a>
						</div>
					</li>
				{% else %}
					<li class="nav-item"><a href="cart" class="nav-link">買い物かご</a></li>
				{% endif %}
			</ul>
			
			<ul class="nav navbar-nav ms-auto" id="ul_guest">
				<li class="nav-item "><a href="#signup" class="nav-link" data-bs-toggle="modal" ><i class="fa fa-user"></i> 新規登録</a></li>
				<li class="nav-item "><a href="#login" class="nav-link" data-bs-toggle="modal"><i class="fa fa-sign-in"></i> ログイン</a></li>
			</ul>
			<ul class="nav navbar-nav ms-auto" id="ul_user">
				<li class="nav-item"><a href="logout" class="nav-link"><i class="fa fa-sign-out"></i>ログアウト</a></li>
				<li class="nav-item">
					<a  class="nav-link" data-placement="bottom" data-bs-toggle="popover" data-bs-trigger="hover focus" style="display: inline-block"
					data-title="{{ email }}" data-bs-content="<a href='order-list'>注文履歴</a><br/><a href='product'>登録情報変更</a>" data-bs-html="true"><i class="fa fa-user-circle "></i></a>
				</li>
			</ul>
			</div>
		</div>
	</div>
</nav>
<!--navigation bar end-->
<!--Login trigger Modal-->
<div class="modal fade" id="login" aria-labelledby="staticBackdropLabel">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content" style="background-color:rgba(255,255,255,0.95)">

		<div class="modal-header">
			<h5 class="modal-title" id="staticBackdropLabel">Login</h5>
			<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
			</button>
		</div>

		<div class="modal-body">
			<form id="frm_login">
				<div class="form-group">
					<label for="email">Email address:</label>
					<input type="email" class="form-control"  name="lemail" placeholder="Enter email" required>
			</div>
			<div class="form-group">
				<label for="pwd">Password:</label>
				<input type="password" class="form-control" id="pwd"  name="lpassword" placeholder="Password" required>
			</div>
			</form>
			<button id="btn_login" class="btn btn-secondary btn-block" >ログイン</button>
			<a href="http://">パスワードをお忘れの方はこちら</a>
		</div>
		<div class="modal-footer">
			<p class="mr-auto">まだアカウントをお持ちでない方はこちら<a href="#signup" data-toggle="modal" data-dismiss="modal" >新規登録</a></p>
			<button type="button" class="btn btn-secondary" data-bs-dismiss="modal" >Close</button>
		</div>
		</div>
	</div>
</div>
<!--Login trigger Model ends-->

<!--Signup model start-->
<div class="modal fade" id="signup">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content" style="background-color:rgba(255,255,255,0.95)">

		<div class="modal-header">
			<h5 class="modal-title">新規登録</h5>
			<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
			</button>
		</div>

		<div class="modal-body">
			<form id="frm_signin" class="needs-validation" novalidate>
			<div class="form-group">
					<label for="email" class="form-label">Email :</label>
					<input type="email" class="form-control"  id="email" name="email" placeholder="Enter email" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" required>
				{% if error is defined %}
					<span class='text-danger'>{{ error }}</span>
				{% endif %}
				<div class="invalid-feedback">
        			emailを正しく入力してください。
      			</div>
			</div>
			<div class="form-group">
				<label for="pwd">Password:</label>
				<input type="password" class="form-control" id="pwd" name="password" placeholder="Password(6文字以上)" pattern=".{6,}" required>
				<div class="invalid-feedback">
        			パスワードを正しく入力してください。
      			</div>
			</div>

			<div class="form-row">
				<div class="form-group col-md-6">
					<label for="validation1">お名前</label>
					<input type="text" class="form-control" id="validation1" name="firstName" placeholder="" required>
				</div>
				<div class="form-group col-md-6">
					<label for="validation2">電話番号</label>
					<input type="text" class="form-control" id="validation2" name="phone" placeholder="">
				</div>
			</div>
			
			<div class="form-check">
				<input type="checkbox" id="checkbox" class="form-check-input" required>
				<label for="checkbox" class="form-check-label">利用契約とプライバシーに同意する</label>
			</div>
			</form>
			<button id="btn_signup" class="btn btn-primary btn-block">登録</button>
		</div>
		<div class="modal-footer">
			<p class="mr-auto">アカウントをお持ちの方はこちら<a href="#login"  data-bs-toggle="modal" data-bs-dismiss="modal">ログイン</a></p>
			<button type="button" class="btn btn-secondary" data-bs-dismiss="modal" >閉じる</button>
		</div>
		</div>
	</div>
</div>
<!--Signup trigger model ends-->

