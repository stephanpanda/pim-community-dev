import * as React from 'react';
import * as $ from 'jquery';

export interface Select2Props {
  data: {
    [choiceValue: string]: string;
  };
  value: string[];
  multiple: boolean;
  readOnly: boolean;
  configuration: any;
  onChange: (value: string[] | string) => void;
}

export default class Select2 extends React.Component<Select2Props> {
  public props: Select2Props;
  private select: React.RefObject<HTMLSelectElement>;

  constructor(props: Select2Props) {
    super(props);

    this.select = React.createRef<HTMLSelectElement>();
  }

  componentDidMount() {
    const $el = $(this.select.current) as any;

    if (undefined !== $el.select2) {
      $el.val(this.props.value).select2(this.props.configuration);
      $el.on('change', (event) => {
        this.props.onChange(event.val);
      });
    }
  }

  componentWillUnmount() {
    const $el = $(this.select.current) as any;

    $el.off('change');
  }

  render(): JSX.Element | JSX.Element[] {
    const {data, value, ...props} = this.props;

    return (
      <select
        {...props}
        ref={this.select}
        multiple={props.multiple}
        disabled={props.readOnly}
        onChange={event => {
          const newValues = Array.prototype.slice // used to convert node list into an array
            .call(event.currentTarget.childNodes)
            .filter((option: HTMLOptionElement) => option.selected)
            .map((option: HTMLOptionElement) => option.value);

          this.props.onChange(this.props.multiple ? newValues : newValues[0]);
        }}
      >
        {Object.keys(data).map((choiceValue: string) => {
          return (
            <option key={choiceValue} value={choiceValue}>
              {data[choiceValue]}
            </option>
          );
        })}
      </select>
    );
  }
}
