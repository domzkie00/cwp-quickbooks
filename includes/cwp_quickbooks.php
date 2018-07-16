<div class="wrap" id="clients-wp-merge-wrap">
    <h1>Clients WP - QuickBooks</h1>
    <br />
    <?php settings_errors() ?>
    <div class="content-wrap">
        <?php
            $cwpquickbooks_settings_options = get_option('cwpquickbooks_settings_options');
            $app_key    = isset($cwpquickbooks_settings_options['app_key']) ? $cwpquickbooks_settings_options['app_key'] : '';
            $app_secret = isset($cwpquickbooks_settings_options['app_secret']) ? $cwpquickbooks_settings_options['app_secret'] : '';
            $app_token  = isset($cwpquickbooks_settings_options['app_token']) ? $cwpquickbooks_settings_options['app_token'] : '';
            $clients_list  = isset($cwpquickbooks_settings_options['clients_list']) ? $cwpquickbooks_settings_options['clients_list'] : '';
        ?>
        <br />
        <form method="post" action="options.php">
            <?php settings_fields( 'cwpquickbooks_settings_options' ); ?>
            <?php do_settings_sections( 'cwpquickbooks_settings_options' ); ?> 
            <table class="form-table">
                <tbody>
                    <tr class="form-field form-required term-name-wrap">
                        <th scope="row">
                            <label>Client ID</label>
                        </th>
                        <td>
                            <input type="text" name="cwpquickbooks_settings_options[app_key]" size="40" width="40" value="<?= $app_key ?>">
                        </td>
                    </tr>
                    <tr class="form-field form-required term-name-wrap">
                        <th scope="row">
                            <label>Client Secret</label>
                        </th>
                        <td>
                            <input type="text" name="cwpquickbooks_settings_options[app_secret]" size="40" width="40" value="<?= $app_secret ?>">
                        </td>
                    </tr>
                    <tr class="form-field form-required term-name-wrap">
                        <th scope="row">
                            <label>Token</label>
                        </th>
                        <td>
                           <textarea rows="5" readonly="" name="cwpquickbooks_settings_options[app_token]"><?= $app_token ?></textarea>
                        </td>
                    </tr>
                    <?php if (!empty($app_token)) { ?>
                        <tr class="form-field form-required term-name-wrap">
                            <th scope="row">
                                <label>QuickBooks Clients</label>
                            </th>
                            <td>
                               <textarea rows="5" readonly="" name="cwpquickbooks_settings_options[clients_list]"><?= $clients_list ?></textarea>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <p>
                <input type="submit" name="save_settings" class="button button-primary" value="Save">
                <?php if (!empty($app_key) && !empty($app_secret)): ?>
                <a href="<?= admin_url( 'edit.php?post_type=bt_client&page=cwp-quickbooks&cwpintegration=quickbooks' ); ?>" class="button button-primary">Get Access Token</a>
                <?php endif; ?>

                <?php if (!empty($app_key) && !empty($app_secret) && !empty($app_token)): ?>
                <a href="<?= admin_url( 'edit.php?post_type=bt_client&page=cwp-quickbooks&cwpintegration=quickbooks&sync_clients=true' ); ?>" class="button button-primary">Sync QuickBooks Clients</a>
                <?php endif; ?>
            </p>
        </form>
    </div>
</div>
