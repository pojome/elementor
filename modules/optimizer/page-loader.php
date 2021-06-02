<?php


namespace Elementor\Modules\Optimizer;

require __DIR__ . '/../../vendor/autoload.php';

use Elementor\Core\Base\Module as BaseModule;
use PHPHtmlParser\Dom;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


class Page_Loader extends BaseModule {
	private $post_id;
	private $page_data;
	private $images;
	private $background_images;

	public function get_name() {
		return 'page-loader';
	}

	public function __construct( $post_id ) {
		parent::__construct();
		$this->post_id = $post_id;

		add_action( 'elementor/frontend/before_enqueue_styles', function() {
			$this->page_data = $this->get_page_data();
			if ( ! $this->page_data ) {
				return;
			}

			$this->images = $this->get_images();
			$this->background_images = $this->get_images( true );

			echo $this->get_critical_styles();

			add_filter( 'style_loader_tag', [ $this, 'delay_loading' ], 10, 4 );

			add_action( 'elementor/widget/render_content', function( $content, $widget ) {
				echo $this->parse_widget_output( $content, $widget );
			}, 10, 2 );

			add_action( 'wp_footer', function() {
				echo $this->get_used_styles();
			} );

			add_action( 'wp_footer', function() {
				echo "
				<script>
					document.addEventListener( 'DOMContentLoaded', () => {
						const imagesPromises = [];
						const bgImagesPromises = [];

					    [ ...document.querySelectorAll( '" . $this->get_images_selectors() . "' ) ].forEach( ( img, i ) => {
					        imagesPromises[ i ] = new Promise( ( resolve, reject ) => {
								const tempImg = document.createElement( 'img' );
								tempImg.setAttribute( 'src', img.getAttribute( 'data-src') );
								tempImg.classList.add( 'optimizer-temp-img' );

								tempImg.addEventListener( 'load', ( e ) => {
						            img.setAttribute( 'src', img.getAttribute( 'data-src' ) );
						            img.removeAttribute( 'data-src' );
									resolve( tempImg );
									e.target.remove();
								} );

								document.body.appendChild( tempImg );
							} )
					    } );

						Promise.all( imagesPromises ).then( () => {
						    // Switch off the css that shows images placeholders
						    // document.querySelector( '.e-optimizer-image-placeholders' ).setAttribute( 'media', 'none' );

						    // Restore src attribute of all images
						    [ ...document.querySelectorAll( '.elementor-element img[data-src]' ) ].forEach( ( img ) => {
						        img.setAttribute( 'src', img.getAttribute( 'data-src' ) );
						        img.removeAttribute( 'data-src' );
						        if ( img.getAttribute( 'data-srcset' ) ) {
						            img.setAttribute( 'srcset', img.getAttribute( 'data-srcset' ) );
						        }
						        img.removeAttribute( 'data-srcset' );
						    } );
						} );

						const background_images = JSON.parse( '" . wp_json_encode( $this->get_images( true ) ) . "' );

					    background_images.forEach( ( el, index ) => {
					        bgImagesPromises[ index ] = new Promise( ( resolve, reject ) => {
								const tempImg = document.createElement( 'img' );
								tempImg.setAttribute( 'src', el.url + '.webp' );
								tempImg.classList.add( 'optimizer-temp-img' );
								tempImg.addEventListener( 'load', ( e ) => {
								    resolve( tempImg );
								    e.target.remove();
								} );
								document.body.appendChild( tempImg );
							} ).then( () => document.querySelector( el.cssSelector ).classList.add( 'bg-loaded' ) );
					    } );

					    const wpadminbar = document.getElementById( 'wpadminbar' );
					    if ( wpadminbar ) {
					    	wpadminbar.style.display = 'block';
					    }
					    document.querySelector( '.e-optimizer-used-style' ).setAttribute( 'media', 'all' );
					} );
				</script>";
			} );
		} );
	}

	public function delay_loading( $html, $handle, $href, $media ) {
		$style_dir = wp_parse_url( $href, PHP_URL_PATH );
		preg_match( '/wp-content/', $style_dir, $matches );
		if ( ! count( $matches ) ) {
			return $html;
		}

		return str_replace(
			"rel='stylesheet'",
			"rel='prefetch' media='none'",
			$html
		);
	}

	private function get_used_styles() {
		return '<style class="e-optimizer-used-style" media="none">' . $this->get_used_css() . '</style>';
	}

	private function get_critical_styles() {
		return '<style class="e-optimizer-critical-style">#wpadminbar{display:none;}' . $this->get_critical_css() . '</style>
		<style class="e-optimizer-image-placeholders">' . $this->get_images_placeholders_css() . '</style>
		<style class="e-optimizer-bg_image-placeholders">' . $this->get_background_images_placeholders_css() . '</style>';
	}

	public function parse_widget_output( $html, $widget ) {
		$images = $this->get_images();

		if ( ! $images ) {
			return $html;
		}

		$widget_id = $widget->get_raw_data()['id'];

		foreach ( $images as $image ) {
			if ( $widget_id === $image['parentWidget'] && $image['critical'] ) {
				$html = str_replace(
					' src="' . $image['src'] . '"',
					' data-src="' . $image['src'] . '.webp" loading="eager"',
					$html
				);
				$html = str_replace( ' srcset="', ' data-srcset="', $html );
			}
		}

		return $html;
	}

	private function get_background_images_selectors() {
		$background_images_selectors = [];

		foreach ( $this->background_images as $image ) {
			$background_images_selectors[] = $image['cssSelector'];
		}

		return implode( ', ', $background_images_selectors );
	}

	private function get_background_images_urls() {
		$background_images_urls = [];

		foreach ( $this->background_images as $image ) {
			$background_images_urls[] = $image['url'];
		}

		return $background_images_urls;
	}

	private function get_images_selectors() {
		$images_selectors = [];

		foreach ( $this->images as $image ) {
			if ( ! $image['critical'] ) {
				continue;
			}
			$img_src = $image['src'] . '.webp';
			$widget_id = $image['parentWidget'];
			$images_selectors[] = '[data-id="' . $widget_id . '"] [data-src="' . $img_src . '"]';
		}

		return implode( ', ', $images_selectors );
	}

	private function get_images_placeholders_css() {
		if ( ! $this->images ) {
			return false;
		}

		$css = '';

		foreach ( $this->images as $image ) {
			$img_src = $image['src'] . '.webp';
			$has_placeholder = isset( $image['placeholder']['data'][0] );
			$url = $has_placeholder ? $image['placeholder']['data'][0] : $img_src;
			$widget_id = $image['parentWidget'];
			$css_selector = '[data-id="' . $widget_id . '"] [data-src="' . $img_src . '"]';
			$image_content_rule = 'content: url("' . $url . '");';
			$image_width_rule = 'min-width:' . $image['clientWidth'] . 'px;';
			$css .= $css_selector . '{' . $image_content_rule . $image_width_rule . '}';
		}

		return $css;
	}

	private function get_background_images_placeholders_css() {
		if ( ! $this->background_images ) {
			return false;
		}

		$css = '';

		foreach ( $this->background_images as $key => $image ) {
			$css .= $image['cssSelector'] . ':not(.bg-loaded)';
			$css .= '{--bg-image-' . $key . ': url("' . $image['placeholder']['data'][0] . '");}';
			$css .= $image['cssSelector'];
			$css .= '{background-image: var(--bg-image-' . $key . ', url("' . $image['url'] . '.webp")) !important;}';
		}

		return $css;
	}

	private function get_images( $background_images = false ) {
		$images_data_key = $background_images ? 'backgroundImages' : 'images';
		$page_data = $this->page_data;
		$images = $page_data && isset( $page_data[ $images_data_key ] ) &&
			is_array( $page_data[ $images_data_key ] ) && 0 < count( $page_data[ $images_data_key ] ) ?
			$page_data[ $images_data_key ] :
			false;

		if ( ! $images ) {
			return false;
		}

		foreach ( $images as $key => $image ) {
			if ( ! $background_images && ! $image['critical'] ) {
				continue;
			}

			$url_key = $background_images ? $image['url'] : $image['src'];

			if ( ! attachment_url_to_postid( $url_key ) ) {
				continue;
			}
			$images[ $key ]['id'] = attachment_url_to_postid( $url_key );
			$images[ $key ]['placeholder']['data'] = get_post_meta(
				$images[ $key ]['id'],
				'_e_optimizer_placeholder_' . $image['placeholder']['size']
			);
		}

		$this->page_data[ $images_data_key ] = $images;

		return $images;
	}

	private function get_used_css() {
		return get_post_meta( get_the_ID(), '_elementor_optimizer_used_css', true );
	}

	private function get_critical_css() {
		return get_post_meta( get_the_ID(), '_elementor_optimizer_critical_css', true );
	}

	private function get_page_data() {
		return get_post_meta( get_the_ID(), '_elementor_analyzer_report', true );
	}
}
