<?php
/**
 * YoastSEO_AMP_Glue plugin file.
 *
 * @package   YoastSEO_AMP_Glue\Sanitizer
 * @copyright 2016 Yoast BV
 * @license   GPL-2.0+
 */

if ( ! defined( 'AMP__DIR__' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

require_once AMP__DIR__ . '/includes/sanitizers/class-amp-base-sanitizer.php';

/**
 * Strips blacklisted tags and attributes from content, on top of the ones the AMP plugin already removes.
 *
 * See following for blacklist:
 * {@link https://github.com/ampproject/amphtml/blob/master/spec/amp-html-format.md#html-tags}
 */
class Yoast_AMP_Blacklist_Sanitizer extends AMP_Base_Sanitizer {

	/**
	 * The actual sanitization function.
	 */
	public function sanitize() {
	    if ( property_exists( 'Yoast_AMP_Blacklist_Sanitizer', 'root_element' ) ) {
            $body = $this->root_element;
        } else {
            $body = $this->get_body_node();
        }

		$this->strip_attributes_recursive( $body );
	}

	/**
	 * Passes through the DOM and removes stuff that shouldn't be there.
	 *
	 * @param DOMNode $node
	 */
	private function strip_attributes_recursive( $node ) {
		if ( $node->nodeType !== XML_ELEMENT_NODE ) {
			return;
		}

		if ( $node->hasAttributes() ) {
			$node_name = $node->nodeName;
			$length    = $node->attributes->length;
			for ( $i = --$length; $i >= 0; $i-- ) {
				$attribute = $node->attributes->item( $i );

				switch ( $node_name ) {
					case 'a':
						$this->sanitize_a_attribute( $node, $attribute );
						break;
					case 'pre':
						$this->sanitize_pre_attribute( $node, $attribute );
						break;
					case 'table':
						$this->sanitize_table_attribute( $node, $attribute );
						break;
					case 'td':
					case 'th':
						$this->sanitize_cell_attribute( $node, $attribute );
						break;
				}
			}
		}

		foreach ( $node->childNodes as $child_node ) {
			$this->strip_attributes_recursive( $child_node );
		}
	}

	/**
	 * Passes through the DOM and strips forbidden tags.
	 *
	 * @param DOMNode $node
	 * @param array   $tag_names
	 */
	private function strip_tags( $node, $tag_names ) {
		foreach ( $tag_names as $tag_name ) {
			$elements = $node->getElementsByTagName( $tag_name );
			$length   = $elements->length;
			if ( 0 === $length ) {
				continue;
			}

			for ( $i = --$length; $i >= 0; $i-- ) {
				$element     = $elements->item( $i );
				$parent_node = $element->parentNode;
				$parent_node->removeChild( $element );

				if ( 'body' !== $parent_node->nodeName && AMP_DOM_Utils::is_node_empty( $parent_node ) ) {
					$parent_node->parentNode->removeChild( $parent_node );
				}
			}
		}
	}

	/**
	 * Sanitizes anchor attributes.
	 *
	 * @param DOMNode $node
	 * @param object  $attribute
	 */
	private function sanitize_a_attribute( $node, $attribute ) {
		$attribute_name = strtolower( $attribute->name );

		if ( 'rel' === $attribute_name && 'nofollow' !== $attribute->value ) {
			$node->removeAttribute( $attribute_name );
		}
	}

	/**
	 * Sanitizes pre tag attributes.
	 *
	 * @param DOMNode $node
	 * @param object  $attribute
	 */
	private function sanitize_pre_attribute( $node, $attribute ) {
		$attribute_name = strtolower( $attribute->name );

		if ( 'line' === $attribute_name ) {
			$node->removeAttribute( $attribute_name );
		}
	}

	/**
	 * Sanitizes td / th tag attributes.
	 *
	 * @param DOMNode $node
	 * @param object  $attribute
	 */
	private function sanitize_cell_attribute( $node, $attribute ) {
		$attribute_name = strtolower( $attribute->name );

		if ( in_array( $attribute_name, array( 'width', 'height' ), true ) ) {
			$node->removeAttribute( $attribute_name );
		}
	}

	/**
	 * Sanitize table tag attributes.
	 *
	 * @param DOMNode $node
	 * @param object  $attribute
	 */
	private function sanitize_table_attribute( $node, $attribute ) {
		$attribute_name = strtolower( $attribute->name );

		if ( in_array( $attribute_name, array( 'border', 'cellspacing', 'cellpadding', 'summary' ), true ) ) {
			$node->removeAttribute( $attribute_name );
		}
	}

}
