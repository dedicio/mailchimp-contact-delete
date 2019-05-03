<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/*
Insert mailchimp-api
Created by Drew McLellan
https://github.com/drewm/mailchimp-api
*/
include( plugin_dir_path( __FILE__ ) . 'include/MailChimp.php');
include( plugin_dir_path( __FILE__ ) . 'include/Batch.php');
use \DrewM\MailChimp\MailChimp;
use \DrewM\MailChimp\Batch;


// Get config data
$plugin_url = plugin_dir_url( __FILE__ );
$site_url = get_bloginfo('url');
$current_user = wp_get_current_user();

global $wpdb;
$table_name = $wpdb->prefix . 'ddc_mcu';
$apikey = get_option('mcu_apikey');
$user_lists = json_decode(get_option('mcu_users'),TRUE);
$msg = '';


// Create new instance
if( $apikey ) {
	$MailChimp 	= new MailChimp($apikey);
	$Batch 			= $MailChimp->new_batch();
} else {
	$msg = "Por favor, cadastre uma MailChimp API Key em Configurações.";
}


if( empty($user_lists[$current_user->user_login]) ) {
	$msg = "Nenhuma lista do MailChimp está vinculada ao seu usuário";
}

function showMessage($msg) {
	return '<div class="notice notice-success is-dismissible"><p>'.$msg.'</p></div>';
}


// Get data from form submit

if( $_REQUEST['mode'] === 'remove' ) : // if mode UNsubscribe

	// Get data from multilines of textarea
	$textarea = trim($_POST['emails']);
	$emails = explode("\n", $textarea);
	$emails = array_filter($emails, 'trim');

	$lists = $user_lists[$current_user->user_login];

	foreach( $lists as $list ) :

		foreach( $emails as $email ) :

			$email_hash = md5(mb_strtolower(trim($email)));

			$remove = $Batch->patch($email,"lists/{$list}/members/{$email_hash}", [
					'status'	=>	'unsubscribed'
				]);

			$wpdb->insert(
				$table_name,
				array(
					'user'		=>	$current_user->user_login,
					'contact'	=>	$email,
					'list'		=>	$list
				)
			);


		endforeach;

	endforeach;

	$result = $Batch->execute();
	$msg = "Contatos descadastrados com sucesso!";


elseif( $_REQUEST['mode'] === 'reinsert' ) :  // if mode REsubscribe


	$id_contacts = implode(',',$_REQUEST['id_contact']);

	$contacts = $wpdb->get_results(
		"
			SELECT *
			FROM {$table_name}
			WHERE id IN({$id_contacts})
		"
	);


	foreach( $contacts as $contact ) :

		$email_hash = md5(mb_strtolower(trim($contact->contact)));

		$reinsert = $Batch->patch($contact->contact,"lists/{$contact->list}/members/{$email_hash}", [
				'status'	=>	'subscribed'
			]);

	endforeach;

	$result = $Batch->execute();

	$wpdb->query(
		"
			DELETE FROM {$table_name}
			WHERE id IN({$id_contacts})
		"
	);

	$msg = "Contatos recadastrados com sucesso!";


endif;



// Get contacts unsubscribes from database
$contacts = $wpdb->get_results(
	"
		SELECT *
		FROM {$table_name}
		WHERE user = '{$current_user->user_login}'
	"
);

?>

<div class="wrap">
	<h1 class="wp-heading-inline">Configurações</h1>
	<hr class="wp-header-end">
	<?php settings_errors(); ?>
	<?php echo ( $msg ) ? showMessage($msg) : ''; ?>

	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
			<div id="post-body-content js">

				<div class="row">
					<div class="col-lg-10">

						<form action="" id="post" method="post" enctype="multipart/form-data" class="general_form">
							<input type="hidden" name="mode" value="remove">

							<div id="postbox-container-2" class="postbox-container meta-box-sortables">
								<div class="postbox">
									<h2 class="hndle ui-sortable-handle"><span>Retirar e-mails da lista</span></h2>
									<div class="inside">

										<p>Digite abaixo os e-mails que deseja retirados das suas listas. <em>(Insira um e-mail por linha)</em></p>

										<div class="row">
											<div class="col-md-10">
												<div class="form-group">
													<textarea name="emails" rows="8" cols="80" id="emails" class="form-control"></textarea>
												</div>
											</div>
											<div class="col-md-2">
												<div class="form-group">
													<button type="submit" name="button" class="button button-primary btn btn-primary" <?php echo (empty($user_lists[$current_user->user_login]) ) ? 'disabled' : ''; ?>>Retirar e-mails</button>
												</div>
											</div>
										</div>

									</div>
								</div>

							</div>

						</form>

					</div>
				</div>


				<div id="postbox-container-2" class="postbox-container meta-box-sortables">

					<div class="row">
						<div class="col-lg-10">
							<div class="postbox">
								<h2 class="hndle ui-sortable-handle"><span>E-mails excluídos</span></h2>
								<div class="inside">

									<form action="" id="post" method="post" enctype="multipart/form-data" class="general_form">
										<input type="hidden" name="mode" value="reinsert">

										<p></p>
										<table class="wp-list-table widefat fixed striped">
											<thead>
												<tr>
													<th>
														<strong>E-mails</strong>
													</th>
													<th>
														<strong>Lista</strong>
													</th>
												</tr>
												<tbody>
													<?php foreach( $contacts as $contact ) : ?>

														<tr>
															<td>
																<label>
																	<input type="checkbox" name="id_contact[]" value="<?php echo $contact->id; ?>">
																	<?php echo $contact->contact; ?>
																</label>
															</td>
															<td>
																<?php echo $contact->list; ?>
															</td>
														</tr>

													<?php endforeach; ?>
												</tbody>
											</thead>
										</table>
										<br>
										<div class="form-group">
											<button type="submit" name="button" class="button button-primary">Retornar e-mails para lista</button>
										</div>

									</form>
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

<style media="screen">
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
</style>
