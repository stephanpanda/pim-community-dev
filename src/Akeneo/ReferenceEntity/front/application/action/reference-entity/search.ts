import {IndexState} from 'akeneoreferenceentity/application/reducer/reference-entity/index';
import ReferenceEntity from 'akeneoreferenceentity/domain/model/reference-entity/reference-entity';
import {Query} from 'akeneoreferenceentity/domain/fetcher/fetcher';
import ReferenceEntityFetcher from 'akeneoreferenceentity/infrastructure/fetcher/reference-entity';
import updateResultsWithFetcher from 'akeneoreferenceentity/application/action/search';

const stateToQuery = async (state: IndexState): Promise<Query> => {
  return {
    locale: undefined === state.user.uiLocale ? '' : state.user.uiLocale,
    limit: state.grid.query.limit,
    page: state.grid.query.page,
    filters: [],
  };
};

export const updateReferenceEntityResults = updateResultsWithFetcher<ReferenceEntity>(
  ReferenceEntityFetcher,
  stateToQuery
);