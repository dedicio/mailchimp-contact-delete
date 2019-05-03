<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

include( plugin_dir_path( __FILE__ ) . 'include/MailChimp.php');
use \DrewM\MailChimp\MailChimp;

$plugin_url = plugin_dir_url( __FILE__ );
$site_url = get_bloginfo('url');

global $wpdb;

if( $_REQUEST['modo'] === 'save' ) :

	if( $_REQUEST['apikey'] ) update_option('mcu_apikey',$_REQUEST['apikey']);

	update_option('mcu_users',json_encode($_POST['user']));

endif;

$users = get_users();


$apikey = get_option('mcu_apikey');
$user_lists = json_decode(get_option('mcu_users'),TRUE);



// Connect to Mailchimp
$mc_api_prefix = substr($apikey,-3);
$mc_api_url = "https://".$mc_api_prefix.".api.mailchimp.com/3.0";
$mc_api_list_url = $mc_api_url . "/lists";

if( $apikey ) :

	$MailChimp = new MailChimp($apikey);
	$lists = $MailChimp->get('lists');

endif;

?>



<div class="wrap">
	<h1 class="wp-heading-inline">Configurações</h1>
	<hr class="wp-header-end">
	<?php settings_errors(); ?>
	<form action="" id="post" method="post" enctype="multipart/form-data" class="general_form">
		<input type="hidden" name="modo" value="save">

		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-2">
				<div id="post-body-content js">

					<div id="postbox-container-2" class="postbox-container meta-box-sortables">

						<div class="row">
							<div class="col-md-7">
								<div class="postbox">
									<h2 class="hndle ui-sortable-handle"><span>Configurar MailChimp</span></h2>
									<div class="inside">
										<div class="form-group">
											<label for="apikey">MailChimp API Key</label>
											<input type="text" name="apikey" value="<?php echo $apikey; ?>" id="apikey" class="form-control"  required>
											<a href="https://mailchimp.com/help/about-api-keys/" target="_blank">Saiba como gerar sua API Key?</a>
										</div>
										<div class="form-group">
											<button type="submit" name="button" class="button button-primary btn btn-primary">Salvar</button>
										</div>
									</div>
								</div>
							</div>
						</div>

					</div>



					<div id="postbox-container-2" class="postbox-container meta-box-sortables">

						<div class="row">
							<div class="col-md-7">
								<div class="postbox">
									<h2 class="hndle ui-sortable-handle"><span>Configurar Usuários</span></h2>
									<div class="inside">
										<p>Selecione quais listas cada usuário terá acesso:</p>
										<table class="wp-list-table widefat fixed striped">
											<thead>
												<tr>
													<th>
														<strong>Usuário</strong>
													</th>
													<th>
														<strong>Listas</strong>
													</th>
												</tr>
												<tbody>
													<?php foreach( $users as $user ) : ?>

														<tr>
															<td>
																<?php echo $user->user_login; ?>
															</td>
															<td>
																<?php
																foreach( $lists['lists'] as $list ) :
																	echo '<p><label><input type="checkbox" name="user['.$user->user_login.'][]" value="'.$list['id'].'" class="form-check"';
																	if( is_array($user_lists[$user->user_login]) ) {
																		echo ( in_array($list['id'],$user_lists[$user->user_login]) ) ? ' checked' : '';
																	}
																	echo ' >' . $list['name'] . '</label></p>';
																endforeach;
																?>
															</td>
														</tr>

													<?php endforeach; ?>
												</tbody>
											</thead>
										</table>
										<br>
										<div class="form-group">
											<button type="submit" name="button" class="button button-primary">Salvar</button>
										</div>
									</div>
								</div>

							</div>
						</div>

					</div>


				</div>
			</div>
			<br class="clear">
		</div>

	</form>
</div>

<!-- <link rel="stylesheet" href="<?php //echo $plugin_url . '/css/bootstrap-grid.min.css';?>"> -->
<style media="screen">
	button, input, optgroup, select, textarea {
	    margin: 0;
	    margin-bottom: 0px;
	    font-family: inherit;
	    font-size: inherit;
	    line-height: inherit;
	}
	input[type="checkbox"], input[type="radio"] {
			box-sizing: border-box;
			padding: 0;
	}
	.text-muted {
	    color: #6c757d !important;
	}
	.form-text {
	    display: block;
	    margin-top: .25rem;
	}
	.small, small {
	    font-size: 80%;
	    font-weight: 400;
	}
	.form-group {
		margin-bottom: 1rem;
	}
	label {
		display: inline-block;
		margin-bottom: .5rem;
	}
	.form-control {
		display: block;
		width: 100%;
		padding: .375rem .75rem;
		font-size: 1rem;
		line-height: 1.5;
		color: #495057;
		background-color: #fff;
		background-clip: padding-box;
		border: 1px solid #ced4da;
		transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
	}
	select.form-control:not([size]):not([multiple]) {
	    height: calc(2.25rem + 2px);
	}
	.form-table th {
		padding-left: 10px;
		background-color:
	}


</style>
