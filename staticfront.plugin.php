<?php

class StaticFront extends Plugin
{
	
	const VERSION= '0.1';
	
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
			Options::set( 'staticfront:page', 'none' );
			Options::set( 'staticfront:blog_index', 'blog' );
		}
	}
	
	/* Set up options */
	public function filter_plugin_config( $actions, $plugin_id )
      {
		if ( $plugin_id == $this->plugin_id() ) {
			$actions[]= _t('Set Home Page', 'staticfront');
		}
		return $actions;
	}

	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			switch ( $action ) {
				case _t('Set Home Page', 'staticfront') :
					$ui= new FormUI( 'staticfront' );
					$page= $ui->add( 'select', 'page', _t('The page to show for the home page: ', 'staticfront') );
					$page->options['none']= _t('Show Normal Posts', 'staticfront');
					foreach( $this->get_all_pages() as $post ) {
						$page->options[$post->slug]= $post->title;
					}
					$blog_index= $ui->add( 'text', 'blog_index', sprintf( _t('Show normal posts at this URL: <b>%s</b>', 'staticfront'), Site::get_url( 'habari', true ) ) );
					$blog_index->add_validator( 'validate_required' );
					$ui->out();
					break;
			}
		}
	}
	
	private function get_all_pages()
	{
		$pages= Posts::get( array( 'content_type' => Post::type('page'), 'nolimit' => 1 ) );
		return $pages;
	}
	
	public function filter_theme_act_display_home( $handled, &$theme )
	{
		$page= Options::get( 'staticfront:page' );
		if ( $page && $page != 'none' ) {
			$post= Post::get( array( 'slug' => $page ) );
			$theme->act_display( array( 'posts' => $post ) );
			return true;
		}
		return false;
	}
	
	public function filter_rewrite_rules( $rules )
	{
		if ( Options::get( 'staticfront:page' ) != 'none' ) {
			$base= trim( Options::get( 'staticfront:blog_index' ) , '/' );
			$rules[] = new RewriteRule(array(
				'name' => 'display_blog_home',
				'parse_regex' => '%^' . $base . '(?:/page/(?P<page>\d+))?/?$%',
				'build_str' => '' . $base . '(/page/{$page})',
				'handler' => 'UserThemeHandler',
				'action' => 'display_home',
				'priority' => 1,
				'rule_class' => RewriteRule::RULE_PLUGIN,
				'is_active' => 1,
				'description' => 'Blog index display for StaticFront' )
				);
		}
		return $rules;
	}
}

?>