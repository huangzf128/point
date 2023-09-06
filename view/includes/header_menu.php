<div class="d-flex align-items-center justify-content-between">
<i class="bi bi-list toggle-sidebar-btn"></i>
	<a href="/" class="logo d-flex align-items-center">
	<img src="images/logo.png" alt="" style="filter: grayscale(0%);">
	<span class="d-none d-lg-block">在庫管理システム</span>
	</a>
	
</div><!-- End Logo -->

<nav class="header-nav ms-auto">
	<ul class="d-flex align-items-center">

	<li class="nav-item dropdown pe-3">

		<a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">

		
		<span class="dropdown-toggle ps-2">{{ auth.name? auth.name: "ユーザ" }}　様</span>
		</a><!-- End Profile Iamge Icon -->

		<ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
		<li class="dropdown-header">
			<h6></h6>
			<span>{{ auth['type'] | default(null) | userType}}</span>
		</li>
		<li>
			<hr class="dropdown-divider">
		</li>

		<li>
			<a class="dropdown-item d-flex align-items-center" href="/">
			<i class="bi bi-gear"></i>
			<span>設定</span>
			</a>
		</li>
		<li>
			<hr class="dropdown-divider">
		</li>

		{% if auth  %}
		<li>
			<a class="dropdown-item d-flex align-items-center" href="logout">
			<i class="bi bi-box-arrow-right"></i>
			<span>ログアウト</span>
			</a>
		</li>
		{% endif %}

		</ul><!-- End Profile Dropdown Items -->
	</li><!-- End Profile Nav -->

	</ul>
</nav><!-- End Icons Navigation -->