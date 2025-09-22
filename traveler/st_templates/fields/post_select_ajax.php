<?php
$post_id        = $_GET['id'] ?? '';
$list_services  = st_listOfServiceSelect();
$list_items     = get_post_meta( $post_id, 'st_upsell', true );
$data_list_item = [];
if ( ! empty( $list_items['list-item'] ) ) {
	foreach ( $list_items['list-item'] as $list_item ) {
		$data_list_item[] = [
			'id'   => $list_item,
			'text' => get_the_title( $list_item ),
		];
	}
}
$data_list_item = json_encode( $data_list_item );


$value_option = '';
if ( ! empty( $list_items['list-item'] ) ) {
	ob_start();
	foreach ( $list_items['list-item'] as $id_post ) { ?>
		<option selected="selected" value="<?php echo esc_attr( $id_post ); ?>"><?php echo get_the_title( $id_post ); ?></option>
		<?php
	}
	$value_option = @ob_get_clean();
}

?>
<div class="form-group st-field-<?php echo esc_attr( $data['type'] ); ?>">
	<label for="<?php echo 'st-field-' . esc_attr( $data['name'] ); ?>"><?php echo balanceTags( $data['label'] ); ?></label>
	<div id="<?php echo 'st-field-' . esc_attr( $data['name'] ); ?>">
		<div class="format-setting-wrap">
			<div class="format-setting-label">
				<h6><?php echo esc_html__( 'Type Service', 'traveler' ); ?></h6>
			</div>
			<div class="format-setting type-select no-desc">
				<div class="format-setting-inner">
					<div class="select-wrapper">
						<select name="type-service" id="type-service" class="form-control option-tree-ui-select stt_select_type">
							<?php foreach ( $list_services as $key => $service ) { ?>
								<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $list_items['type-service'] ?? '', $key ); ?>><?php echo esc_html( $service ); ?></option>
							<?php } ?>
						</select>
					</div>
				</div>
			</div>
		</div>
		<div class="format-setting-inner">
			<div class="option-tree-ui-post_select_ajax-input-wrap">
				<select
					multiple
					id="st_upsell"
					class='upsell_select_ajax'
					data-list="<?= esc_attr( $data_list_item ) ?>"
					data-placeholder="<?= __( 'Select items for Upsell', 'traveler' ) ?>"
					data-post-type=''
					type=hidden
					name='list-item[]'
				>
				<?php echo $value_option ?>
				</select>
			</div>
		</div>
	</div>
</div>
