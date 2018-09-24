import * as React from 'react';
import Value from 'akeneoenrichedentity/domain/model/record/value';
import TextData, {create} from 'akeneoenrichedentity/domain/model/record/data/text';
import Flag from 'akeneoenrichedentity/tools/component/flag';
import {createLocaleFromCode} from 'akeneoenrichedentity/domain/model/locale';
import {ConcreteTextAttribute} from 'akeneoenrichedentity/domain/model/attribute/type/text';
import RichTextEditor from 'akeneoenrichedentity/application/component/app/rich-text-editor';

const View = ({value, onChange}: {value: Value; onChange: (value: Value) => void}) => {
  if (!(value.data instanceof TextData && value.attribute instanceof ConcreteTextAttribute)) {
    return null;
  }

  const onValueChange = (text: string) => {
    const newData = create(text);
    if (newData.equals(value.data)) {
      return;
    }

    const newValue = value.setData(newData);

    onChange(newValue);
  };

  return (
    <React.Fragment>
      {value.attribute.isTextarea.booleanValue() ? (
        value.attribute.isRichTextEditor.booleanValue() ? (
          <RichTextEditor value={value.data.stringValue()} onChange={onValueChange} />
        ) : (
          <textarea
            className={`AknTextareaField ${value.attribute.valuePerLocale ? 'AknTextareaField--localizable' : ''}`}
            value={value.data.stringValue()}
            onChange={(event: React.ChangeEvent<HTMLTextAreaElement>) => {
              onValueChange(event.currentTarget.value);
            }}
          />
        )
      ) : (
        <input
          className={`AknTextField AknTextField--withBottomBorder ${
            value.attribute.valuePerLocale ? 'AknTextField--localizable' : ''
          }`}
          value={value.data.stringValue()}
          onChange={(event: React.ChangeEvent<HTMLInputElement>) => {
            onValueChange(event.currentTarget.value);
          }}
        />
      )}
      {value.attribute.valuePerLocale ? (
        <Flag locale={createLocaleFromCode(value.locale.stringValue())} displayLanguage={false} />
      ) : null}
    </React.Fragment>
  );
};

export const view = View;
