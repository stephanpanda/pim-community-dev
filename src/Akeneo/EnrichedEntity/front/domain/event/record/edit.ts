import ValidationError from 'akeneoenrichedentity/domain/model/validation-error';
import File from 'akeneoenrichedentity/domain/model/file';
import Record from 'akeneoenrichedentity/domain/model/record/record';

export const recordEditionReceived = (record: Record) => {
  return {type: 'RECORD_EDITION_RECEIVED', record: record.normalize()};
};

export const recordEditionUpdated = (record: Record) => {
  return {type: 'RECORD_EDITION_UPDATED', record: record.normalize()};
};

export const recordEditionLabelUpdated = (value: string, locale: string) => {
  return {type: 'RECORD_EDITION_LABEL_UPDATED', value, locale};
};

export const recordEditionImageUpdated = (image: File) => {
  return {type: 'RECORD_EDITION_IMAGE_UPDATED', image: image.normalize()};
};

export const recordEditionSubmission = () => {
  return {type: 'RECORD_EDITION_SUBMISSION'};
};

export const recordEditionSucceeded = () => {
  return {type: 'RECORD_EDITION_SUCCEEDED'};
};

export const recordEditionErrorOccured = (errors: ValidationError[]) => {
  return {type: 'RECORD_EDITION_ERROR_OCCURED', errors};
};