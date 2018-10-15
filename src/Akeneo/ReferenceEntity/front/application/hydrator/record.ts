import ReferenceEntity from 'akeneoreferenceentity/domain/model/record/record';
import {validateKeys} from 'akeneoreferenceentity/application/hydrator/hydrator';
import denormalizeRecord from 'akeneoreferenceentity/application/denormalizer/record';

export default (backendRecord: any): ReferenceEntity => {
  backendRecord.image = undefined === backendRecord.image ? null : backendRecord.image;

  const expectedKeys = ['identifier', 'reference_entity_identifier', 'code', 'labels', 'image', 'values'];

  validateKeys(backendRecord, expectedKeys, 'The provided raw record seems to be malformed.');

  return denormalizeRecord(backendRecord);
};
