/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import * as $ from 'jquery';

const __ = require('oro/translator');
const BaseController = require('pim/controller/front');
const FetcherRegistry = require('pim/fetcher-registry');
const Router = require('pim/router');

interface Families {
  [index: string]: object;
}

/**
 * Attribute mapping index controller
 * This controller will load the first mapping, and do a redirect to the edit page.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class IndexAttributeMappingController extends BaseController {
  public renderForm(): object {
    return FetcherRegistry.getFetcher('family')
      .fetchAll()
      .then((families: Families) => {
        if (0 === Object.keys(families).length) {
          return $.Deferred().reject({
            status: 404,
            statusText: __('akeneo_suggest_data.entity.attributes_mapping.module.index.error'),
          });
        }

        const firstFamilyCode = Object.keys(families).sort()[0];

        Router.redirectToRoute('akeneo_suggest_data_attributes_mapping_edit', {familyCode: firstFamilyCode});

        return undefined;
      });
  }
}

export = IndexAttributeMappingController;