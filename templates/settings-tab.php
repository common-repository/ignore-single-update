<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="ispu-settings">
    <section>
        <table>
            <?php 
foreach ( $this->_default_settings as $optionName => $defaultValues ) {
    if ( isset( $defaultValues['capacity'] ) && !current_user_can( $defaultValues['capacity'] ) ) {
        continue;
    }
    if ( !isset( $defaultValues['type'] ) ) {
        continue;
    }
    ?>
                <?php 
    ?>
                <tr<?php 
    if ( isset( $defaultValues['conditional'] ) ) {
        ?>
                    class="hidden-<?php 
        echo esc_attr( $optionName );
        if ( !$this->_settings[$defaultValues['conditional']] ) {
            ?> ispu-hidden<?php 
        }
        ?>"
                <?php 
    }
    ?>>
                    <?php 
    $description = '';
    if ( isset( $defaultValues['desc'] ) ) {
        $description = $defaultValues['desc'];
    }
    ?>
                    <th scope="row"><?php 
    echo $defaultValues['text'];
    ?> <span
                                class="dashicons dashicons-info-outline" tabindex="0"
                                data-desc="<?php 
    echo esc_attr( $description );
    ?>"
                                data-option="<?php 
    echo esc_attr( $optionName );
    ?>"></span>
                    </th>
                    <td>
                        <?php 
    switch ( $defaultValues['type'] ) {
        case "switch":
            ?>
                                <label class="ispu-switch">
                                    <input type="checkbox" id="<?php 
            echo esc_attr( $optionName );
            ?>"
                                           name="<?php 
            echo esc_attr( $optionName );
            ?>"<?php 
            if ( isset( $defaultValues['shows'] ) ) {
                echo ' data-shows="' . esc_attr( $defaultValues['shows'] ) . '"';
            }
            ?> <?php 
            if ( $this->_settings[$optionName] ) {
                echo 'checked';
            }
            ?>>
                                    <span class="ispu-slider"></span>
                                </label>
                                <?php 
            break;
        case "number":
            ?>
                                <div class="ispu-input-holder">
                                    <input class="ispu-number-input<?php 
            if ( isset( $defaultValues['unit'] ) ) {
                echo ' has-unit';
            }
            ?>" <?php 
            if ( isset( $defaultValues['range'] ) ) {
                ?>
                                           min="<?php 
                echo (int) $defaultValues['range'][0];
                ?>"
                                           max="<?php 
                echo (int) $defaultValues['range'][1];
                ?>"
                                           <?php 
            }
            ?>type="number"
                                           data-current="<?php 
            echo esc_attr( $this->_settings[$optionName] );
            ?>"
                                           id="<?php 
            echo esc_attr( $optionName );
            ?>" name="<?php 
            echo esc_attr( $optionName );
            ?>"
                                           value="<?php 
            echo esc_attr( $this->_settings[$optionName] );
            ?>"><?php 
            if ( isset( $defaultValues['unit'] ) ) {
                ?>
                                        <div class="ispu-setting-unit"><?php 
                echo esc_html( $defaultValues['unit'] );
                ?></div>
                                    <?php 
            }
            ?>
                                    <a href="#" class="ispu-button ispu-button-reverse-colors ispu-save-button ispu-option-<?php 
            echo esc_attr( $optionName );
            ?>" data-option="<?php 
            echo esc_attr( $optionName );
            ?>">
                                        <?php 
            esc_html_e( 'Save' );
            ?>
                                    </a>
                                </div>
                                <?php 
            break;
        case "multiselect":
            wp_enqueue_script(
                'igspu-multiselect',
                $this->_path . '/res/jquery.multiselect/jquery.multiselect.js',
                [],
                IGSPU_VERSION
            );
            wp_enqueue_style(
                'igspu-multiselect',
                $this->_path . '/res/jquery.multiselect/jquery.multiselect.css',
                [],
                IGSPU_VERSION
            );
            ?>
                                <select multiple class="ispu-select" id="<?php 
            echo esc_attr( $optionName );
            ?>"
                                        name="<?php 
            echo esc_attr( $optionName );
            ?>[]">
                                    <?php 
            foreach ( $defaultValues['choices'] as $text => $value ) {
                ?>
                                        <option value="<?php 
                echo esc_attr( $value );
                ?>"<?php 
                if ( in_array( $value, $this->_settings[$optionName] ) ) {
                    echo ' selected';
                }
                ?>>
                                            <?php 
                echo esc_html( $text );
                ?>
                                        </option>
                                    <?php 
            }
            ?>
                                </select>
                                <?php 
            break;
        case "select":
            ?>
                                <select class="ispu-select" id="<?php 
            echo esc_attr( $optionName );
            ?>"
                                    <?php 
            if ( isset( $defaultValues['shows'] ) ) {
                echo ' data-shows="' . esc_attr( $defaultValues['shows'] ) . '"';
            }
            ?>
                                        name="<?php 
            echo esc_attr( $optionName );
            ?>">
                                    <?php 
            foreach ( $defaultValues['choices'] as $text => $value ) {
                ?>
                                        <option value="<?php 
                echo esc_attr( $value );
                ?>"<?php 
                if ( $this->_settings[$optionName] == $value ) {
                    echo ' selected';
                }
                ?>
                                            <?php 
                if ( $optionName == 'autopilot' && $value == '3' && $this->_settings['patch_versions'] ) {
                    echo ' class="ispu-hidden"';
                }
                ?>>
                                            <?php 
                echo esc_html( $text );
                ?>
                                        </option>
                                    <?php 
            }
            ?>
                                </select>
                                <?php 
            break;
        case "text":
        case "password":
            ?>
                                <?php 
            ?>
                                <input type="<?php 
            echo esc_attr( $defaultValues['type'] );
            ?>" class="ispu-text-input"
                                       data-current="<?php 
            echo esc_attr( $this->_settings[$optionName] );
            ?>"
                                       id="<?php 
            echo esc_attr( $optionName );
            ?>" name="<?php 
            echo esc_attr( $optionName );
            ?>"
                                       value="<?php 
            echo esc_attr( $this->_settings[$optionName] );
            ?>">
                                <?php 
            if ( $defaultValues['type'] == 'password' ) {
                ?>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                     viewBox="0 0 16 16">
                                    <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/>
                                    <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
                                </svg>
                            <?php 
            }
            ?>
                                <a class="ispu-save-button ispu-button ispu-button-reverse-colors ispu-option-<?php 
            echo esc_attr( $optionName );
            ?>" data-option="<?php 
            echo esc_attr( $optionName );
            ?>">
                                    <?php 
            esc_html_e( 'Save' );
            ?>
                                </a>
                                <?php 
            break;
        case "premium":
            $this->_upgrade_to_premium_button( 'upgrade' );
            break;
    }
    ?>
                        <?php 
    if ( isset( $defaultValues['after']['enabled'] ) && $defaultValues['after']['enabled'] === true ) {
        $functionName = '_' . $optionName . '_after';
        if ( $defaultValues['after']['premium'] === true ) {
            $functionName .= '__premium_only';
        }
        $this->{$functionName}( $defaultValues );
        ?>
                        <?php 
    }
    ?>
                    </td>
                </tr>
            <?php 
}
?>
        </table>
    </section>
</div>
