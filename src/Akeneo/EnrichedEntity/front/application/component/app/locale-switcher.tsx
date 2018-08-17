import * as React from 'react';
import Locale from 'akeneoenrichedentity/domain/model/locale';
import __ from 'akeneoenrichedentity/tools/translator';
import Flag from 'akeneoenrichedentity/tools/component/flag';
import Dropdown, {DropdownElement} from 'akeneoenrichedentity/application/component/app/dropdown';

const LocaleItemView = ({
  element,
  isActive,
  onClick,
}: {
  element: DropdownElement;
  isActive: boolean;
  onClick: (element: DropdownElement) => void;
}): JSX.Element => {
  const menuLinkClass = `AknDropdown-menuLink ${isActive ? `AknDropdown-menuLink--active` : ''}`;

  return (
    <div className={menuLinkClass} data-identifier={element.identifier} onClick={() => onClick(element)}>
      <span className="label">
        <Flag locale={element.original} displayLanguage />
      </span>
    </div>
  );
};

const LocaleButtonView = ({
  selectedElement,
  onClick,
}: {
  selectedElement: DropdownElement;
  onClick: () => void;
}) => (
  <div
    className="AknActionButton AknActionButton--withoutBorder"
    data-identifier={selectedElement.identifier}
    onClick={onClick}
  >
    {__('Locale')}:&nbsp;
    <span className="AknActionButton-highlight" data-identifier={selectedElement.identifier}>
      <Flag locale={selectedElement.original} displayLanguage />
    </span>
    <span className="AknActionButton-caret"></span>
  </div>
);

const LocaleSwitcher = ({
  localeCode,
  locales,
  onLocaleChange,
}: {
  localeCode: string;
  locales: Locale[];
  onLocaleChange: (locale: Locale) => void;
}) => {
  return (
    <Dropdown
      elements={locales.map((locale: Locale) => {
        return {
          identifier: locale.code,
          label: locale.label,
          original: locale,
        };
      })}
      label={__('Locale')}
      selectedElement={localeCode}
      ItemView={LocaleItemView}
      ButtonView={LocaleButtonView}
      onSelectionChange={(locale: DropdownElement) => onLocaleChange(locale.original)}
      className="locale-switcher"
    />
  );
};

export default LocaleSwitcher;
