<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/* @var string $type Contains either "permanent", "ignored", "forced", or "expired", as defined in table.php (argument of _render_table_body)
 * @var array $updates The array of ignored plugin updates
 */
$tbodyClass = $type;
$buttonText = __( 'Stop Ignoring', 'ignore-single-update' );
if ( $type == 'expired' ) {
    $buttonText = __( 'Delete' );
}
switch ( $type ) {
    case "expired":
        $headingText = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-info-circle-fill" viewBox="0 0 16 16">
    <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/>
</svg>' . esc_html__( 'The following ignored plugin updates have expired. You may leave them, or delete them (risk-free) from the database', 'ignore-single-update' );
        break;
    case "permanent":
        $text = esc_html__( 'The following plugin updates are PERMANENTLY IGNORED, and may represent security risks', 'ignore-single-update' );
        $headingText = '<svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" role="img" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
    <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
</svg>' . $text;
        break;
    case "forced":
        $headingText = '<svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" role="img" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8 4a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 4zm.002 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/>
</svg>' . esc_html__( 'The following plugin updates are currently automatically ignored, as per your Autopilot settings', 'ignore-single-update' );
        break;
    default:
        $headingText = '<svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" role="img" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8 4a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 4zm.002 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/>
</svg>' . esc_html__( 'The following plugin updates are currently ignored until they expire, or until the next version releases', 'ignore-single-update' );
}
?>
<tbody class="<?php 
echo $tbodyClass;
?>">
<tr class="heading-text">
    <td colspan="5"><?php 
echo $headingText;
?></td>
</tr>
<tr class="heading-columns">
    <td><?php 
esc_html_e( 'Plugin' );
?></td>
    <td><?php 
esc_html_e( 'Installed Version', 'ignore-single-update' );
?></td>
    <td><?php 
esc_html_e( 'Ignored Version', 'ignore-single-update' );
?></td>
    <td><?php 
if ( $type == 'expired' ) {
    esc_html_e( 'Reason', 'ignore-single-update' );
} else {
    esc_html_e( 'Ignored Until', 'ignore-single-update' );
}
?></td>
    <td><?php 
if ( $type == 'forced' ) {
    esc_html_e( 'Reason', 'ignore-single-update' );
}
?></td>
</tr>
<?php 
foreach ( $updates as $pluginFile => $data ) {
    if ( !array_key_exists( $pluginFile, $this->_updates ) && $type != 'expired' ) {
        continue;
    }
    $until = esc_html__( 'Next version', 'ignore-single-update' );
    if ( $type == 'forced' ) {
        if ( version_compare( $data['latest_known_version']['version'], $data['ignored_version'], '>' ) ) {
            $data['ignored_version'] = $data['latest_known_version']['version'];
        }
        $until = wp_date( $this->_datetime_format, strtotime( '+' . $this->_settings['autopilot_days'] . ' days', $data['since'] ) );
    } elseif ( $data['until'] ) {
        if ( $data['until'] == 'Forever' || $this->_get_difference_in_days( time(), $data['until'] ) >= 5000 ) {
            $until = '<strong>' . esc_html__( 'FOREVER', 'ignore-single-update' ) . '</strong>';
            if ( $data['ignored_version'] == 'Any' ) {
                $data['ignored_version'] = esc_html__( 'Any ulterior version', 'ignore-single-update' );
            }
        } else {
            $until = wp_date( $this->_datetime_format, $data['until'] );
        }
    }
    $detailLink = '';
    if ( $type == 'ignored' || $type == 'forced' ) {
        $pluginSlug = $this->_get_plugin_slug( $pluginFile );
        require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        $api = plugins_api( 'plugin_information', [
            'slug' => wp_unslash( $pluginSlug ),
        ] );
        if ( isset( $api->download_link ) ) {
            $detailLink = ' <a href="' . esc_url( self_admin_url( 'plugin-install.php?tab=plugin-information&section=changelog&plugin=' . $pluginSlug ) ) . '&TB_iframe=true&width=600&height=550" class="thickbox open-plugin-details-modal" data-title="' . esc_attr( $data['name'] ) . '">' . esc_html__( 'View details' ) . '</a>';
        }
    }
    ?>
    <tr data-plugin="<?php 
    echo esc_attr( $pluginFile );
    ?>">
        <td><?php 
    echo esc_html( $data['name'] );
    ?></td>
        <td><?php 
    echo esc_html( $data['current-version'] );
    ?></td>
        <td><?php 
    echo esc_html( $data['ignored_version'] ) . $detailLink;
    ?></td>
        <td><?php 
    if ( $type === 'expired' ) {
        if ( $data['reason'] == esc_html__( 'Vulnerability detected', 'ignore-single-update' ) ) {
            if ( $data['ignored_version'] != $data['current-version'] ) {
                echo '<span style="color:red;font-weight:bold">' . esc_html__( $data['reason'], 'ignore-single-update' ) . '</span>';
            } else {
                echo esc_html__( 'Plugin updated', 'ignore-single-update' );
            }
        } else {
            echo esc_html__( $data['reason'], 'ignore-single-update' );
        }
    } else {
        echo $until;
    }
    ?></td>
        <td>
            <?php 
    if ( $type == 'forced' ) {
        $updateTypes = [
            '1' => esc_html__( 'Major version', 'ignore-single-update' ),
            '2' => esc_html__( 'Minor version', 'ignore-single-update' ),
            '3' => esc_html__( 'Patch version', 'ignore-single-update' ),
        ];
        echo $updateTypes[$data['update_type']];
    } else {
        ?>
                <a href="#" class="unignore ispu-button ispu-table-button ispu-button-reverse-colors" data-plugin="<?php 
        echo esc_attr( $pluginFile );
        ?>"
                   data-type="<?php 
        echo esc_attr( $type );
        ?>"><?php 
        echo esc_html( $buttonText );
        ?></a>
            <?php 
    }
    ?>
        </td>
    </tr>
<?php 
}
if ( $type == 'expired' && count( $updates ) > 1 ) {
    ?>
    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td>
            <a href="#" class="unignore ispu-button ispu-table-button ispu-button-reverse-colors" data-plugin="<?php 
    echo esc_attr( implode( ',', array_keys( $updates ) ) );
    ?>"
               data-type="delete-all"><?php 
    esc_html_e( 'Delete All', 'ignore-single-update' );
    ?></a>
        </td>
    </tr>
<?php 
}
?>
</tbody>
<tbody class="empty"></tbody>
