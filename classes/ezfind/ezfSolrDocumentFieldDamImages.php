<?php
/*
 * Indexing logic for the datatype dam_images - ezfind uses it
 * 
 */

class ezfSolrDocumentFieldDamImages extends ezfSolrDocumentFieldBase
{
	/**
	 * @see ezfSolrDocumentFieldBase::getData()
	 *
	 * @return array
	 */
	public function getData()
	{
		$return = array();

		if( $this->ContentObjectAttribute->attribute( 'has_content' ) )
		{
			$content = $this->ContentObjectAttribute->attribute( 'content' );

			foreach( $content as $ratio_identifier => $values )
			{
				$return[ 'attr_dam_images_'. $ratio_identifier .'____ms' ] = $values[ 'url' ];
				$return[ 'attr_dam_images_'. $ratio_identifier .'____s' ] = $values[ 'alt' ];
			}
		}

		return $return;
	}
}

