import attributeSaver from 'akeneoenrichedentity/infrastructure/saver/attribute';
import {
  attributeEditionSucceeded,
  attributeEditionErrorOccured,
  attributeEditionStart as attributeEditionStartEvent,
  attributeEditionSubmission,
  attributeEditionCancel,
} from 'akeneoenrichedentity/domain/event/attribute/edit';
import AttributeIdentifier from 'akeneoenrichedentity/domain/model/attribute/identifier';
import ValidationError, {createValidationError} from 'akeneoenrichedentity/domain/model/validation-error';
import {EditState} from 'akeneoenrichedentity/application/reducer/enriched-entity/edit';
import {notifyAttributeSaveFailed} from 'akeneoenrichedentity/application/action/attribute/notify';
import {updateAttributeList} from 'akeneoenrichedentity/application/action/attribute/list';
import {denormalizeAttribute, NormalizedAttribute} from 'akeneoenrichedentity/domain/model/attribute/attribute';
import AttributeCode from 'akeneoenrichedentity/domain/model/code';

export const saveAttribute = (dismiss: boolean = true) => async (
  dispatch: any,
  getState: () => EditState
): Promise<void> => {
  if (getState().attribute.isSaving) {
    return;
  }

  dispatch(attributeEditionSubmission());
  const normalizedAttribute = getState().attribute.data;
  const attribute = denormalizeAttribute(normalizedAttribute);

  try {
    let errors = await attributeSaver.save(attribute);

    if (errors) {
      const validationErrors = errors.map((error: ValidationError) => createValidationError(error));
      dispatch(attributeEditionErrorOccured(validationErrors));
      dispatch(notifyAttributeSaveFailed());

      return;
    }
  } catch (error) {
    dispatch(attributeEditionErrorOccured([]));
    dispatch(notifyAttributeSaveFailed());

    return;
  }

  dispatch(attributeEditionSucceeded());
  if (dismiss) {
    dispatch(attributeEditionCancel());
  }
  await dispatch(updateAttributeList());

  return;
};

export const attributeEditionStartByCode = (attributeCode: AttributeCode) => async (
  dispatch: any,
  getState: () => EditState
): Promise<void> => {
  const state = getState();
  if (null === state.attributes.attributes) {
    return;
  }

  const attributeToEdit = state.attributes.attributes.find(
    (attribute: NormalizedAttribute) => attribute.code === attributeCode.stringValue()
  );

  dispatch(attributeEditionStart(attributeToEdit));
};

export const attributeEditionStartByIdentifier = (attributeIdentifier: AttributeIdentifier) => async (
  dispatch: any,
  getState: () => EditState
): Promise<void> => {
  const state = getState();
  if (null === state.attributes.attributes) {
    return;
  }

  const attributeToEdit = state.attributes.attributes.find(
    (attribute: NormalizedAttribute) => attribute.identifier === attributeIdentifier.stringValue()
  );

  dispatch(attributeEditionStart(attributeToEdit));
};

export const attributeEditionStart = (attribute: NormalizedAttribute | undefined) => async (
  dispatch: any,
  getState: () => EditState
): Promise<void> => {
  if (undefined === attribute) {
    return;
  }

  const attributeState = getState().attribute;

  if (attributeState.isDirty) {
    await dispatch(saveAttribute(false));
  }

  if (!getState().attribute.isDirty) {
    dispatch(attributeEditionStartEvent(denormalizeAttribute(attribute)));
  }
};
