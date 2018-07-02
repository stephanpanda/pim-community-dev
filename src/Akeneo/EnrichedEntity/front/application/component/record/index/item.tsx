import * as React from 'react';
import Record from 'akeneoenrichedentity/domain/model/record/record';
import { getImageShowUrl } from 'akeneoenrichedentity/tools/media-url-generator';
const router = require('pim/router');

export default ({
  record,
  locale,
  isLoading = false,
  onRedirectToRecord
}: {
  record: Record;
  locale: string;
  isLoading?: boolean;
  position: number;
} & {
  onRedirectToRecord: (record: Record) => void;
}) => {
  const path =
    '' !== record.getIdentifier().stringValue()
      ? `#${router.generate('akeneo_enriched_entities_record_edit', {
          enrichedEntityIdentifier: record.getEnrichedEntityIdentifier().stringValue(),
          identifier: record.getIdentifier().stringValue()
        })}`
      : '';

  return (
    <tr
      className={`AknGrid-bodyRow AknGrid-bodyRow--withoutTopBorder ${
        isLoading ? "AknLoadingPlaceHolder" : ""
      }`}
      onClick={event => {
        event.preventDefault();

        onRedirectToRecord(record);

        return false;
      }}
    >
      <td className="AknGrid-bodyCell">
        <img className="AknGrid-image" src={getImageShowUrl(null, "thumbnail_small")} title="" />
      </td>
      <td className="AknGrid-bodyCell">
        <a
          href={path}
          title={record.getLabel(locale)}
          data-identifier={record.getIdentifier().stringValue()}
          onClick={event => {
            event.preventDefault();

            onRedirectToRecord(record);

            return false;
          }}
        >
          {record.getLabel(locale)}
        </a>
      </td>
      <td className="AknGrid-bodyCell">
        <a
          href={path}
          title={record.getLabel(locale)}
          data-identifier={record.getIdentifier().stringValue()}
          onClick={event => {
            event.preventDefault();

            onRedirectToRecord(record);

            return false;
          }}
        >
          {record.getIdentifier().stringValue()}
        </a>
      </td>
      <td className="AknGrid-bodyCell AknGrid-bodyCell--actions action-cell">
        <div className="AknButtonList AknButtonList--right" />
      </td>
    </tr>
  );
};