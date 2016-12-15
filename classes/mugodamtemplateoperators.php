<?php

class MugoDamTemplateOperators
{
	function operatorList()
	{
		return array(
				'image_url',
		);
	}

	function namedParameterPerOperator()
	{
		return true;
	}

	function namedParameterList()
	{
		//PEK: consider to change order: protocol before image_ratio_identifier
		return array(
			'image_url' => array(
				'alias' => array(
					'type' => 'string',
					'required' => false,
					'default' => '',
				),
				'image_ratio_identifier' => array(
					'type' => 'array',
					'required' => false,
					'default' => 'standard',
				),
				'protocol' => array(
					'type' => 'string',
					'required' => false,
					'default' => 'none',
				),
			),
		);
	}

	function modify( $tpl,
	                 $operatorName,
	                 $operatorParameters,
	                 $rootNamespace,
	                 $currentNamespace,
	                 &$operatorValue,
	                 $namedParameters )
	{		
		switch ( $operatorName )
		{
			case 'image_url':
			{
				$attribute = $operatorValue;

				$alias = isset( $namedParameters[ 'alias' ] ) ? $namedParameters[ 'alias' ] : '';
				$image_ratio_identifier = isset( $namedParameters[ 'image_ratio_identifier' ] ) ? $namedParameters[ 'image_ratio_identifier' ] : 'standard';
				$protocol = isset( $namedParameters[ 'protocol' ] ) ? $namedParameters[ 'protocol' ] : 'none';

				if( $operatorValue instanceof eZContentObjectAttribute )
				{
					$operatorValue = dam_imagesType::getImageUrlByAttribute(
							$operatorValue,
							$alias,
							$image_ratio_identifier,
							$protocol );
				}
				else
				{
					$operatorValue = dam_imagesType::getImageUrl(
							$operatorValue,
							$alias,
							$protocol );
				}
			}
			break;

			default:
		}
	}
}
