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

			$altValues = array();
			foreach( $content as $ratio_identifier => $values )
			{
				$return[ 'attr_dam_images_'. $ratio_identifier .'____ms' ] = $values[ 'url' ];
				$altValues[] = $values[ 'alt' ];
			}
		}

		if( !empty( $altValues) )
		{
			// attr_image_t is used for the fulltext search
			$return[ 'attr_image_t' ] = implode( ' ', $altValues );
		}

		return $return;
	}
}

