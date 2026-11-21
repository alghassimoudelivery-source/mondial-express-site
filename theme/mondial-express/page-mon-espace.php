<?php
/**
 * Espace client.
 *
 * @package MondialExpress
 */

get_header();
the_post();
$user = wp_get_current_user();
me_page_hero( is_user_logged_in() ? sprintf( /* translators: %s: prénom */ __( 'Bonjour %s', 'mondial-express' ), esc_html( $user->display_name ) ) : get_the_title(), is_user_logged_in() ? __( 'Vos demandes, vos dossiers et vos documents au même endroit.', 'mondial-express' ) : __( 'Créez votre compte pour suivre vos dossiers et envoyer vos documents en toute sécurité.', 'mondial-express' ) );
?>
<div class="container page-body">
	<?php echo me_account_notice(); // phpcs:ignore ?>
	<?php if ( ! is_user_logged_in() ) : ?>
		<div class="account">
			<section class="account__box">
				<h2><?php esc_html_e( 'Se connecter', 'mondial-express' ); ?></h2>
				<?php wp_login_form( array( 'redirect' => get_permalink(), 'label_username' => __( 'E-mail ou identifiant', 'mondial-express' ), 'label_password' => __( 'Mot de passe', 'mondial-express' ), 'label_remember' => __( 'Rester connecté', 'mondial-express' ), 'label_log_in' => __( 'Se connecter', 'mondial-express' ) ) ); ?>
				<p><a href="<?php echo esc_url( wp_lostpassword_url( get_permalink() ) ); ?>"><?php esc_html_e( 'Mot de passe oublié ?', 'mondial-express' ); ?></a></p>
			</section>
			<section class="account__box account__box--alt">
				<h2><?php esc_html_e( 'Créer mon compte', 'mondial-express' ); ?></h2>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="me-form">
					<input type="hidden" name="action" value="me_inscription">
					<?php wp_nonce_field( 'me_inscription', 'me_nonce' ); ?>
					<p class="hp" aria-hidden="true"><label>Site <input name="site_web" tabindex="-1" autocomplete="off"></label></p>
					<div class="me-form__grid me-form__grid--1">
						<div class="fld"><label for="ins-nom"><?php esc_html_e( 'Nom complet', 'mondial-express' ); ?></label><input id="ins-nom" name="nom" required autocomplete="name"></div>
						<div class="fld"><label for="ins-tel"><?php esc_html_e( 'Téléphone / WhatsApp', 'mondial-express' ); ?></label><input id="ins-tel" name="telephone" type="tel" required autocomplete="tel"></div>
						<div class="fld"><label for="ins-mail"><?php esc_html_e( 'E-mail', 'mondial-express' ); ?></label><input id="ins-mail" name="email" type="email" required autocomplete="email"></div>
						<div class="fld"><label for="ins-pass"><?php esc_html_e( 'Mot de passe (8 caractères minimum)', 'mondial-express' ); ?></label><input id="ins-pass" name="mot_de_passe" type="password" minlength="8" required autocomplete="new-password"></div>
						<div class="fld"><button class="btn btn--block" type="submit"><?php esc_html_e( 'Créer mon compte', 'mondial-express' ); ?></button></div>
					</div>
				</form>
			</section>
		</div>
	<?php else : ?>
		<?php
		$dossiers = get_posts( array( 'post_type' => 'me_dossier', 'numberposts' => 20, 'meta_key' => '_me_client', 'meta_value' => get_current_user_id() ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
		$demandes = get_posts( array( 'post_type' => 'me_demande', 'numberposts' => 10, 'meta_key' => '_me_client', 'meta_value' => get_current_user_id() ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
		?>
		<div class="dash">
			<div class="dash__main">
				<h2><?php esc_html_e( 'Mes dossiers', 'mondial-express' ); ?></h2>
				<?php if ( ! $dossiers ) : ?>
					<p class="empty"><?php esc_html_e( "Aucun dossier pour l'instant. Dès qu'un conseiller ouvre votre dossier, il apparaît ici.", 'mondial-express' ); ?> <a href="<?php echo esc_url( home_url( '/demande-de-devis/' ) ); ?>"><?php esc_html_e( 'Faire une demande', 'mondial-express' ); ?></a></p>
				<?php endif; ?>
				<?php foreach ( $dossiers as $d ) : ?>
					<?php echo me_dossier_timeline( $d->ID ); // phpcs:ignore ?>
					<form class="upload" method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="me_document"><input type="hidden" name="dossier" value="<?php echo (int) $d->ID; ?>">
						<?php wp_nonce_field( 'me_document_' . $d->ID, 'me_nonce' ); ?>
						<label class="upload__drop"><?php echo me_icon( 'upload' ); ?> <span><b><?php esc_html_e( 'Envoyer un document', 'mondial-express' ); ?></b> <?php esc_html_e( 'PDF, JPG ou PNG, 8 Mo max.', 'mondial-express' ); ?></span><input type="file" name="fichier" accept=".pdf,.jpg,.jpeg,.png" required></label>
						<input name="libelle" placeholder="<?php esc_attr_e( 'Nom du document (ex. Passeport)', 'mondial-express' ); ?>" aria-label="<?php esc_attr_e( 'Nom du document', 'mondial-express' ); ?>">
						<button class="btn btn--sm" type="submit"><?php esc_html_e( 'Envoyer', 'mondial-express' ); ?></button>
					</form>
				<?php endforeach; ?>
			</div>
			<aside class="dash__side">
				<h2><?php esc_html_e( 'Mes demandes', 'mondial-express' ); ?></h2>
				<ul class="req-list">
					<?php foreach ( $demandes as $r ) : ?>
						<li><b><?php echo esc_html( get_the_title( $r ) ); ?></b><small><?php echo esc_html( get_the_date( '', $r ) ); ?> · <?php echo esc_html( ucfirst( str_replace( '-', ' ', get_post_meta( $r->ID, '_me_statut', true ) ) ) ); ?></small></li>
					<?php endforeach; ?>
					<?php if ( ! $demandes ) : ?><li><small><?php esc_html_e( 'Aucune demande envoyée avec ce compte.', 'mondial-express' ); ?></small></li><?php endif; ?>
				</ul>
				<a class="btn btn--block" href="<?php echo esc_url( home_url( '/demande-de-devis/' ) ); ?>"><?php esc_html_e( 'Nouvelle demande', 'mondial-express' ); ?></a>
				<a class="btn btn--outline btn--block" href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>"><?php esc_html_e( 'Se déconnecter', 'mondial-express' ); ?></a>
			</aside>
		</div>
	<?php endif; ?>
</div>
<?php
get_footer();
