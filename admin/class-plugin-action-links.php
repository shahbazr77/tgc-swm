<?php
if( !class_exists('WB_Plugin_Action_Links') ){
	/**
	 * 
	 */
	class WB_Plugin_Action_Links{
		public $plugin_file;
		public $review_link;
		public $upgrade_pro_link;
		public $settings_page;
		function __construct( $plugin_file, $review_link, $upgrade_pro_link, $settings_page='' )
		{
			$this->plugin_file = $plugin_file;
			$this->review_link = $review_link;
			$this->upgrade_pro_link = $upgrade_pro_link;
			$this->settings_page = $settings_page;
			// add_filter( 'plugin_row_meta', [ $this, 'plugin_row_meta'], 10, 3 );
			add_filter( 'plugin_action_links', [ $this, 'plugin_action_links'], 10, 3 );
		}

		public function plugin_row_meta( $links, $file, $data ){
			if ( isset($file) && $file === $this->plugin_file ) {
				$new_links = array(
						'review' => '<a href="'.$this->review_link.'" target="_blank" class="wb-ebaic-color-red" style="color: #e49b00;"><strong class="wb-ebaic-extra-bold wb-ebaic-font-16">Leave a Review</strong></a>',
						);
				
				$links = array_merge( $links, $new_links );
			}
			
			return $links;
		}

		public function plugin_action_links( $links, $file, $data ){
			if ( isset($file) && $file === $this->plugin_file ) {

				if( isset($this->settings_page) && !empty($this->settings_page) ){
					$new_links = array(
							'admin_settings' => '<a href="'.$this->settings_page.'" >Settings</a>',
							);
					
					$links = array_merge( $new_links, $links );
				}

				$new_links = array(
						'review' => '<a href="'.$this->upgrade_pro_link.'" target="_blank" style="color: red;" class="wb-ebaic-color-red" ><b class="wb-ebaic-extra-bold wb-ebaic-font-16" >Upgrade to Pro</b></a>',
						);
				
				$links = array_merge( $links, $new_links );

			}
			
			return $links;
		}
	}
}