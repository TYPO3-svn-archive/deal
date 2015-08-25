<?php

if ( !defined( 'TYPO3_MODE' ) )
{
  die( 'Access denied.' );
}

// #i0036, 150825, dwildt, +
$TCA[ "tx_my_apartmentsforrent" ][ "ctrl" ][ "tx_deal" ] = array(
  'marketplaces' => array(
    'immo24' => array(
      'ctrl' => array(
        'channels' => array(
          '10000' => TRUE, // 10000 = IS24: if true, expose will published at this channel by default
          '10001' => TRUE, // 10001 = Homepage: if true, expose will published at this channel by default
        ),
        'fields' => array(
          'attachments' => array(
            'images' => array(
              'files' => array(
                'field' => 'images',
                'type' => 'Picture', // Picture, PDFDocument or Link
              ),
            ),
            'floorplan' => array(
              'files' => array(
                'field' => 'me_grundriss_pdf',
                'type' => 'PDFDocument', // Picture, PDFDocument or Link
              ),
            ),
          ),
          'attachmentssandbox' => array(
            'images' => array(
              'files' => array(
                'field' => 'images',
                'type' => 'Picture', // Picture, PDFDocument or Link
              ),
            ),
            'floorplan' => array(
              'files' => array(
                'field' => 'me_grundriss_pdf',
                'type' => 'Picture', // Picture, PDFDocument or Link
              ),
            ),
          ),
          'contact' => 'feusers_uid', // obligate: field with relation to the contact table
          'contactsandbox' => 'feusers_uid', // obligate: field with relation to the contact table
          'immo24id' => 'immo24id', // obligate
          'immo24idsandbox' => 'immo24idSandbox', // obligate
          'immo24log' => 'immo24log', // obligate
          'immo24logsandbox' => 'immo24log', // obligate
          'immo24timestamp' => 'immo24tstamp', // obligate
          'immo24timestampsandbox' => 'immo24tstampSandbox', // obligate
          'immo24url' => 'immo24url', // obligate
          'immo24urlsandbox' => 'immo24urlSandbox', // obligate
        ),
        'values' => array(
          'urlexpose' => 'https://www.immobilienscout24.de/scoutmanager/exposemanager', // obligate
          'urlexposesandbox' => 'https://www.sandbox-immobilienscout24.de/scoutmanager/exposemanager',
        ),
        'sql' => array(
          'andWhere' => ' AND category LIKE "Wohnen" AND immoart LIKE "Mietwohnung"',
        ),
      ),
      'fields' => array( // order of fields is obligate!
        'externalId' => array(
          'field' => 'uid',
        ),
        'title' => array(
          'field' => 'title',
        ),
        'address' => array(
          'field' => array(
            'street' => array(
              'field' => 'street',
            ),
            'houseNumber' => array(
              'field' => 'hausnr',
            ),
            'postcode' => array(
              'field' => 'zip',
            ),
            'city' => array(
              'field' => 'city',
            ),
            'wgs84Coordinate' => array(
              'field' => array(
                'latitude' => array(
                  'field' => 'lat',
                ),
                'longitude' => array(
                  'field' => 'lon',
                ),
              ),
            ),
          ),
        ),
        'descriptionNote' => array(
          'field' => '',
        ),
        'furnishingNote' => array(
          'field' => '',
        ),
        'locationNote' => array(
          'field' => '',
        ),
        'otherNote' => array(
          'field' => '',
        ),
        'showAddress' => array(
          'field' => '',
          'default' => TRUE,
//          'mapping' => array(
//            '0' => 'NOT_APPLICABLE',
//            '1' => 'YES',
//          ),
        ),
        'contact' => array(
          'field' => 'feusers_uid', // NULL is obligated
          'attribute' => 'id',
          'pattern' => '^[\d]*$', // True: all numbers
          'patternsample' => 'all numbers like 123, 47102',
        ),
        'floor' => array(
          'field' => '',
        ),
        'lift' => array(
          'field' => '',
          'mapping' => array(
            '0' => 'NOT_APPLICABLE',
            '1' => 'YES',
          ),
        ),
        'cellar' => array(
          'field' => '',
          'mapping' => array(
            '0' => 'NOT_APPLICABLE',
            '1' => 'YES',
          ),
        ),
        'handicappedAccessible' => array(
          'field' => '',
        ),
        'lastRefurbishment' => array(
          'field' => '',
        ),
        'interiorQuality' => array(
          'field' => '',
          'default' => 'NO_INFORMATION',
          'mapping' => array(
            'simple' => 'SIMPLE',
            'normal' => 'NORMAL',
            'luxury' => 'LUXURY',
            'sophisticated' => 'SOPHISTICATED',
            'default' => 'NO_INFORMATION'
          ),
        ),
        'constructionYear' => array(
          'field' => '',
        ),
        'freeFrom' => array(
          'field' => '',
        ),
        'heatingType' => array(
          'field' => '',
          'default' => 'NO_INFORMATION',
          'mapping' => array(
            'floor' => 'SELF_CONTAINED_CENTRAL_HEATING',
            'central' => 'CENTRAL_HEATING',
            'stove' => 'STOVE_HEATING',
            'default' => 'NO_INFORMATION'
          ),
        ),
        'buildingEnergyRatingType' => array(
          'field' => '',
          'default' => 'NO_INFORMATION',
          'mapping' => array(
            'required' => 'ENERGY_REQUIRED',
            'consumption' => 'ENERGY_CONSUMPTION',
            'default' => 'NO_INFORMATION'
          ),
        ),
        'thermalCharacteristic' => array(
          'field' => '',
        ),
        'energyConsumptionContainsWarmWater' => array(
          'field' => '',
          'mapping' => array(
            '0' => 'NOT_APPLICABLE',
            '1' => 'YES',
          ),
        ),
        'numberOfFloors' => array(
          'field' => '',
        ),
        'usableFloorSpace' => array(
          'field' => '',
        ),
        'numberOfBedRooms' => array(
          'field' => '',
        ),
        'guestToilet' => array(
          'field' => '',
          'mapping' => array(
            '0' => 'NOT_APPLICABLE',
            '1' => 'YES',
          ),
        ),
        'baseRent' => array(
          'field' => 'me_bsp_preis',
        ),
        'totalRent' => array(
          'field' => '',
        ),
        'serviceCharge' => array(
          'field' => '',
        ),
        'petsAllowed' => array(
          'field' => '',
          'mapping' => array(
            '0' => 'NO',
            '1' => 'YES',
          ),
        ),
//        'price' => array(
//          'field' => array(
//          ),
//        ),
//        'buyPriceCurrency' => array(
//          'field' => '',
//          'default' => 'EUR',
//        ),
        'livingSpace' => array(
          'field' => 'flaeche',
        ),
        'numberOfRooms' => array(
          'field' => 'me_zimmer',
        ),
        'balcony' => array(
          'field' => '',
          'mapping' => array(
            '0' => 'NO',
            '1' => 'YES',
          ),
        ),
        'garden' => array(
          'field' => '',
        ),
        'courtage' => array(
          'field' => array(
            'hasCourtage' => array(
              'field' => 'g_courtage',
              'mapping' => array(
                '0' => 'NO',
                '1' => 'YES',
              ),
            ),
            'courtage' => array(
              'field' => '',
            ),
            'courtageNote' => array(
              'field' => '',
            ),
          ),
        ),
      ),
    ),
  )
);
