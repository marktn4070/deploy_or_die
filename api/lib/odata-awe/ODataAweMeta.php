<?php
/*
ODataAwe is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/odata-awe/blob/main/LICENSE
*/
trait ODataAweMetaTrait {
	private function Metadata() {
		$dom = new DOMDocument('1.0', 'utf-8');

		$namespaceURIs = [
		  'xmlns' => 'http://docs.oasis-open.org/odata/ns/edm',
		  'edmx' => 'http://docs.oasis-open.org/odata/ns/edmx',
		];

		$edmx = $dom->createElementNS($namespaceURIs['edmx'],'edmx:Edmx');
		$dom->appendChild($edmx);

		$attribute = $dom->createAttribute('Version');
		$attribute->value = '4.0';
		$edmx->appendChild($attribute);

		$dataservices = $dom->createElementNS($namespaceURIs['edmx'],'edmx:DataServices');
		$edmx->appendChild($dataservices);

		$schema = $dom->createElementNS($namespaceURIs['xmlns'],'Schema');
		$dataservices->appendChild($schema);
		$attribute = $dom->createAttribute('Namespace');
		$attribute->value = $this->options['namespace'];
		$schema->appendChild($attribute);

		foreach($this->functions as $key => $function) {
			$entitytype = $dom->createElement('EntityType');
			$schema->appendChild($entitytype);

			$attribute = $dom->createAttribute('Name');
			$attribute->value = $key;
			$entitytype->appendChild($attribute);

			foreach($function['field'] as $field => $var) {
				if(isset($var['key']) && $var['key']===true) {
					if(empty($keyelement)) {
						$keyelement = $dom->createElement('Key');
						$entitytype->appendChild($keyelement);
					}
					$propertyref = $dom->createElement('PropertyRef');
					$keyelement->appendChild($propertyref);

					$attribute = $dom->createAttribute('Name');
					$attribute->value = $field;
					$propertyref->appendChild($attribute);
				}
				$property = $dom->createElement('Property');
				$entitytype->appendChild($property);

				$attribute = $dom->createAttribute('Name');
				$attribute->value = $field;
				$property->appendChild($attribute);

				$attribute = $dom->createAttribute('Type');
				switch($var['type']) {
					case 'int':
						$attribute->value = 'Edm.Int32';
						break;
					case 'float':
						$attribute->value = 'Edm.Decimal';
						break;
					case 'bool':
						$attribute->value = 'Edm.Boolean';
						break;
					case 'string':
						$attribute->value = 'Edm.String';
						break;
					case 'datetime':
						$attribute->value = 'Edm.DateTimeOffset';
						break;
					case 'date':
						$attribute->value = 'Edm.Date';
						break;
					case 'time':
						$attribute->value = 'Edm.TimeOfDay';
						break;
				}

				$property->appendChild($attribute);

				$attribute = $dom->createAttribute('Nullable');
				$attribute->value = (isset($var['null']) && $var['null']===true) ? 'true' : 'false';
				$property->appendChild($attribute);
			}
			$keyelement = false;
		}

		$entitycontainer = $dom->createElement('EntityContainer');
		$schema->appendChild($entitycontainer);

		$attribute = $dom->createAttribute('Name');
		$attribute->value = 'Container';
		$entitycontainer->appendChild($attribute);

		foreach($this->functions as $key => $function) {
			$entityset = $dom->createElement('EntitySet');
			$entitycontainer->appendChild($entityset);

			$attribute = $dom->createAttribute('Name');
			$attribute->value = $key;
			$entityset->appendChild($attribute);

			$attribute = $dom->createAttribute('EntityType');
			$attribute->value = $this->options['namespace'].'.'.$key;
			$entityset->appendChild($attribute);
		}

		header('Content-Type: application/xml');
		header('OData-Version: 4.0');
		echo $dom->saveXML();
	}
}
