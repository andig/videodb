{*
  Login template
  $Id: login.tpl,v 1.3 2013/03/12 19:13:18 andig2 Exp $
*}

<!-- {$smarty.template} -->

<div class="row">
	<div class="small-12 large-4 columns small-centered panel">

		{if $error}
		<div class="alert-box">
			{$error}
			<a href="#" class="close">&times;</a>
		</div>
		{/if}

		<p>{$lang.enterusername}</p>

		<form action="login.php" method="post">
			<input type="hidden" value="{$refer}" name="refer"/>

			<div class="row">
				<div class="small-4 columns">
					<p>{$lang.username}:</p>
				</div><!-- col -->

				<div class="small-8 columns">
					<input type="text" name="username" />
				</div><!-- col -->
			</div><!-- row -->

			<div class="row">
				<div class="small-4 columns">
					<p>{$lang.password}:</p>
				</div><!-- col -->

				<div class="small-8 columns">
					<input type="password" name="password" />
				</div><!-- col -->
			</div><!-- row -->

			<div class="row">
				<div class="small-12 columns">
					<p>
						<label class="inline"><input type="checkbox" name="permanent" value="1" />{$lang.stayloggedin}</label>
					</p>
				</div><!-- col -->
			</div><!-- row -->

			<div class="row">
				<div class="small-12 columns small-centered">
					<input type="submit" class="button" value="{$lang.login}" />
				</div><!-- col -->
			</div><!-- row -->
		</form>

	</div><!-- col -->
</div><!-- row -->
