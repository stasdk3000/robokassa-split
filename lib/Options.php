<?php

declare(strict_types=1);

namespace Rbsp;

class Options
{

    protected string $metaPart = 'rbsp_vendor_payment_part';
    protected string $metaRobokassaID = 'rbsp_vendor_robokassa_id';
    protected string $postType = 'hp_vendor';
    protected string $domain = 'wc-robosplit';

    public function __construct()
    {
        add_action( 'add_meta_boxes_' . $this->postType, [ $this, 'addMetabox'] );
        add_action( 'save_post', [ $this, 'saveMeta' ], 10, 2 );
    }

    public function addMetabox()
    {
        add_meta_box(
            'rbsp_metabox',
            'Robokassa Split',
            [$this, 'metaboxCallback'],
            $this->postType,
            'normal',
            'default'
        );
    }

    public function metaboxCallback( $post )
    {

        $vendorPaymentPart = get_post_meta( $post->ID, $this->metaPart, true );
        $vendorRobokassaId = get_post_meta( $post->ID, $this->metaRobokassaID, true );

        echo '<table class="form-table">
            <tbody>
                <tr>
                    <th>
                        <div>
                        <label for="rbsp_vendor_payment_part">' . __("Доля продавца в %", $this->domain) . '</label>
                        <div class="hp-tooltip"><span class="hp-tooltip__icon dashicons dashicons-editor-help"></span><div class="hp-tooltip__text">
                            ' . __("Введите проценты от 1 до 100", $this->domain) . '
                        </div></div>

                        </div>
                    </th>
                    <td><input type="number" max="100" min="0" step="0.1" id="rbsp_vendor_payment_part" name="rbsp_vendor_payment_part" value="' . esc_attr( $vendorPaymentPart ) . '" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="rbsp_vendor_robokassa_id">' . __("Robokassa ID продавца", $this->domain) . '</label></th>
                    <td><input type="text" id="rbsp_vendor_robokassa_id" name="rbsp_vendor_robokassa_id" value="' . esc_attr( $vendorRobokassaId ) . '" class="regular-text"></td>
                </tr>
            </tbody>
	    </table>';
    }

    public function saveMeta( $post_id, $post )
    {
        $post_type = get_post_type_object( $post->post_type );

        if ( ! current_user_can( $post_type->cap->edit_post, $post_id ) ) {
            return $post_id;
        }

        if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
            return $post_id;
        }

        if( $this->postType !== $post->post_type ) {
            return $post_id;
        }

        if( isset( $_POST[ $this->metaPart ] ) ) {
            update_post_meta( $post_id, $this->metaPart, sanitize_text_field( $_POST[ $this->metaPart ] ) );
        }

        if( isset( $_POST[ $this->metaRobokassaID ] ) ) {
            update_post_meta( $post_id, $this->metaRobokassaID, sanitize_text_field( $_POST[ $this->metaRobokassaID ] ) );
        }

        return $post_id;
    }
}