<ul class="sidebar-nav" id="sidebar-nav">

<li class="nav-item">
  <a class="nav-link " href="/">
	<i class="bi bi-grid"></i>
	<span>Dashboard</span>
  </a>
</li><!-- End Dashboard Nav -->

{% if auth %}
<ul id="forms-nav" class="nav-content " data-bs-parent="#sidebar-nav">
  	<li class="nav-heading">操作</li>
	<li>
		<a href="in">
			<i class="bi bi-box-arrow-in-down-right"></i>
			<span>入庫</span>
		</a>
	</li>
	<li>
		<a href="out">
			<i class="bi bi-box-arrow-up-right"></i>
			<span>出庫</span>
		</a>
	</li>
	<li>
		<a href="report">
			<i class="bi bi-printer"></i>
			<span>帳票印刷</span>
		</a>
	</li>
	<li>
		<a href="reportTotal">
			<i class="bi bi-printer"></i>
			<span>帳票印刷(月計)</span>
		</a>
	</li>	
</ul>

{% if auth['type'] >= 9 %}

<ul id="forms-nav" class="nav-content " data-bs-parent="#sidebar-nav">
	<li class="nav-heading">メンテナンス</li>
	<li>
		<a class="nav-link collapsed" href="inventory">
			<i class="bi bi-house-fill"></i>
			<span>在庫管理</span>
		</a>
	</li>

	{% if auth['type'] == 99 %}
	<li>
		<a class="nav-link collapsed" href="import">
			<i class="bi bi-journal-text"></i>
			<span>取込</span>
		</a>
	</li>
	{% endif %}
</ul>

<ul id="forms-nav" class="nav-content " data-bs-parent="#sidebar-nav">
	<li class="nav-heading">マスタ管理</li>
<!-- 
	<li>
		<a class="nav-link collapsed" href="category">
			<i class="bi bi-ui-checks-grid"></i>
			<span>分類管理</span>
		</a>
	</li>
 -->
	<li>
		<a class="nav-link collapsed" href="warehouse">
			<i class="bi bi-bank2"></i>
			<span>倉庫管理</span>
		</a>
	</li><!-- End Register Page Nav -->	
	<li>
		<a class="nav-link collapsed" href="item">
			<i class="bi bi-upc-scan"></i>
			<span>商品管理</span>
		</a>
	</li><!-- End F.A.Q Page Nav -->
	<li>
		<a class="nav-link collapsed" href="customer">
			<i class="bi bi-person"></i>
			<span>取引先管理</span>
		</a>
	</li><!-- End Blank Page Nav -->

	
	<li class="nav-item">
		<a class="nav-link collapsed" href="user">
			<i class="bi bi-box-arrow-in-right"></i>
			<span>ユーザ管理</span>
		</a>
	</li><!-- End Login Page Nav -->
	
</ul>
{% endif %}

{% else %}

<li class="nav-item">
  <a class="nav-link collapsed" href="pages-login.html">
	<i class="bi bi-box-arrow-in-right"></i>
	<span>Login</span>
  </a>
</li><!-- End Login Page Nav -->

<!-- 
<li class="nav-item">
  <a class="nav-link collapsed" href="pages-blank.html">
	<i class="bi bi-file-earmark"></i>
	<span>Blank</span>
  </a>
</li>
 -->
{% endif %}

<li class="nav-item">
  <a class="nav-link collapsed" href="/manual">
	<i class="bi bi-patch-question"></i>
	<span>Help</span>
  </a>
</li>

<!-- <a class="nav-link collapsed" data-bs-target="#forms-nav-maintenance" data-bs-toggle="collapse" href="#">
	<i class="bi bi-align-middle"></i><span>メンテナンス</span><i class="bi bi-chevron-down ms-auto"></i>
</a>
<ul id="forms-nav-maintenance" class="nav-content collapse " data-bs-parent="#sidebar-nav">

</ul> -->

</ul>