<?php

class eZOEXMLInputMugoDam extends eZOEXMLInput
{
	function &inputTagXML( &$tag, $currentSectionLevel, $tdSectionLevel = null )
	{
		$tagName = $tag instanceof DOMNode ? $tag->nodeName : '';

		if( $tagName == 'embed' || $tagName == 'embed-inline' )
		{
			$view = $tag->getAttribute( 'view' );
			$size = $tag->getAttribute( 'size' );
			$alignment = $tag->getAttribute( 'align' );
			$className = $tag->getAttribute( 'class' );
			$objectID = $tag->getAttribute( 'object_id' );
			$nodeID = $tag->getAttribute( 'node_id' );

			if( !$size )
			{
				$contentIni = eZINI::instance( 'content.ini' );
				$size = $contentIni->variable( 'ImageSettings', 'DefaultEmbedAlias' );
			}

			if ( !$view )
			{
				$view = $tagName;
			}

			if ( is_numeric( $objectID ) )
			{
				$object = eZContentObject::fetch( $objectID );
				$idString = 'eZObject_' . $objectID;
			}
			elseif ( is_numeric( $nodeID ) )
			{
				$node = eZContentObjectTreeNode::fetch( $nodeID );
				$object = $node instanceof eZContentObjectTreeNode ? $node->object() : false;
				$idString  = 'eZNode_' . $nodeID;
			}

			if( $object instanceof eZContentObject )
			{
				$mugoDamIni = eZINI::instance( 'mugo_dam.ini' );

				$imageClassIdentifier = $mugoDamIni->variable( 'ImageClass', 'ClassIdentifier' );
				$imageAttributeIdentifier = $mugoDamIni->variable( 'ImageClass', 'ImageAttributeIdentifier' );

				if( $object->attribute( 'class_identifier' ) === $imageClassIdentifier )
				{
					$data_map = $object->attribute( 'data_map' );

					if( isset( $data_map[ $imageAttributeIdentifier ] ) && $data_map[ $imageAttributeIdentifier ]->attribute( 'has_content' ) )
					{
						$srcString = dam_imagesType::getImageUrlByAttribute( $data_map[ $imageAttributeIdentifier ], $size );
					}

					if ( !isset( $srcString ) )
					{
						$srcString = self::getDesignFile( 'images/tango/mail-attachment32.png' );
					}

					$objectAttr = '';
					$objectAttr .= ' alt="' . $size . '"';
					$objectAttr .= ' view="' . $view . '"';

					if ( $alignment === 'center' )
						$objectAttr .= ' align="middle"';
					else if ( $alignment )
						$objectAttr .= ' align="' . $alignment . '"';

					if ( $className != '' )
						$objectAttr .= ' class="' . $className . '"';

					return '<img id="' . $idString . '" title="' . $object->attribute( 'name' ) . '" src="' .
						$srcString . '"' . $objectAttr . ' />';
				}
			}
		}

		return parent::inputTagXML( $tag, $currentSectionLevel, $tdSectionLevel );
	}
}
