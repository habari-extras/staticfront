<?php

class StaticFront extends Plugin
{
	
	const VERSION= '0.1';
	const OPTION_NAME= 'staticfront:page';
	
	/**
	 * Return plugin metadata for this plugin
	 *
	 * @return array Plugin metadata
	 */
	public function info()
	{
		return array(
			'url' => 'http://habariproject.org',
			'name' => 'StaticFront',
			'license' => 'Apache License 2.0',
			'author' => 'Habari Community',
			'version' => self::VERSION,
			'description' => 'Allows you to set a page to show as the home page.'
		);
	}
	
	public function action_plugin_activation( $file )
	{
		if ( $file == $this->get_file() ) {
			Options::set( self::OPTION_NAME, 'none' );
		}
	}
	
	/* Set up options */
	public function filter_plugin_config( $actions, $plugin_id )
      {
		if ( $plugin_id == $this->plugin_id() ) {
			$actions[]= _t('Set Home Page');
		}
		return $actions;
	}

	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			switch ( $action ) {
				case _t('Set Home Page') :
					$ui= new FormUI( 'staticfront' );
					$control= $ui->add( 'select', 'page', 'The page to show for the home page: ' );
					$control->options['none']= _t('Show Normal Posts');
					foreach( $this->get_all_pages() as $page ) {
						$control->options[$page->slug]= $page->title;
					}
					$ui->out();
					break;
			}
		}
	}
	
	private function get_all_pages()
	{
		$pages= Posts::get( array( 'content_type' => Post::type('page') ) );
		return $pages;
	}
	
	public function filter_theme_act_display_home( $handled, &$theme )
	{
		$page= Options::get( self::OPTION_NAME );
		if ( $page && $page != 'none' ) {
			$post= Post::get( array( 'slug' => $page ) );
			$theme->act_display( array( 'posts' => $post ) );
			return true;
		}
		return false;
	}
}

?>