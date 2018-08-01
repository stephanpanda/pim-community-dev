import {createEnrichedEntity as enrichedEntityFactory} from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import Identifier from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import LabelCollection from 'akeneoenrichedentity/domain/model/label-collection';
import enrichedEntitySaver from 'akeneoenrichedentity/infrastructure/saver/enriched-entity';
import {
  enrichedEntityCreationSucceeded,
  enrichedEntityCreationErrorOccured,
} from 'akeneoenrichedentity/domain/event/enriched-entity/create';
import {
  notifyEnrichedEntityWellCreated,
  notifyEnrichedEntityCreateFailed,
} from 'akeneoenrichedentity/application/action/enriched-entity/notify';
import ValidationError, {createValidationError} from 'akeneoenrichedentity/domain/model/validation-error';
import {IndexState} from 'akeneoenrichedentity/application/reducer/enriched-entity/index';
import {redirectToEnrichedEntity} from 'akeneoenrichedentity/application/action/enriched-entity/router';

export const createEnrichedEntity = () => async (dispatch: any, getState: () => IndexState): Promise<void> => {
  const {code, labels} = getState().create.data;
  const enrichedEntity = enrichedEntityFactory(Identifier.create(code), LabelCollection.create(labels));
  try {
    let errors = await enrichedEntitySaver.create(enrichedEntity);

    if (errors) {
      const validationErrors = errors.map((error: ValidationError) => createValidationError(error));
      dispatch(enrichedEntityCreationErrorOccured(validationErrors));
      dispatch(notifyEnrichedEntityCreateFailed());

      return;
    }
  } catch (error) {
    dispatch(notifyEnrichedEntityCreateFailed());

    return;
  }

  dispatch(enrichedEntityCreationSucceeded());
  dispatch(notifyEnrichedEntityWellCreated());
  dispatch(redirectToEnrichedEntity(enrichedEntity));

  return;
};