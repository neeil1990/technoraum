import {Text, Type} from 'main.core';
import {FieldTypes} from "./fieldtypes";
import {EnumItem} from "./enumitem";

export class Field
{
	constructor(data: Object)
	{
		this.data = data;
	}

	getId(): number
	{
		return Text.toInteger(this.data.ID);
	}

	getData(): Object
	{
		return this.data;
	}

	getName(): string
	{
		return this.data.FIELD;
	}

	getTypeId(): string
	{
		return this.data.USER_TYPE_ID;
	}

	getEnumeration(): Array
	{
		if(!Type.isArray(this.data.ENUM))
		{
			this.data.ENUM = [];
		}

		return this.data.ENUM;
	}

	saveEnumeration(items: EnumItem[])
	{
		this.data.ENUM = [];
		let sort = 100;

		items.forEach((item) =>
		{
			this.data.ENUM.push({
				VALUE: item.getValue(),
				SORT: sort,
			});

			sort += 100;
		});
	}

	static getTitleFields(): Array
	{
		return Array.from([
			'EDIT_FORM_LABEL',
			'LIST_COLUMN_LABEL',
			'LIST_FILTER_LABEL',
		]);
	}

	getTitle(): string
	{
		const titleFields = Field.getTitleFields();

		const titleFieldsCount = titleFields.length;

		for(let index = 0; index < titleFieldsCount; index++)
		{
			if(Type.isString(this.data[titleFields[index]]) && this.data[titleFields[index]].length > 0)
			{
				return this.data[titleFields[index]];
			}
		}

		return this.getName();
	}

	setTitle(title: string)
	{
		if(Type.isString(title) && title.length > 0)
		{
			Field.getTitleFields().forEach((label) =>
			{
				this.data[label] = title;
			});
			if(this.getTypeId() === FieldTypes.boolean)
			{
				this.data.SETTINGS.LABEL_CHECKBOX = title;
			}
		}
	}

	isSaved(): boolean
	{
		return (this.getId() > 0);
	}

	isShowAlways(): boolean
	{
		return (this.data.IS_SHOW_ALWAYS === 'Y');
	}

	setIsShowAlways(isShowAlways)
	{
		this.data.IS_SHOW_ALWAYS = (Text.toBoolean(isShowAlways) === true ? 'Y' : 'N');
	}

	isMultiple(): boolean
	{
		return (this.data.MULTIPLE === 'Y');
	}

	setIsMultiple(isMultiple)
	{
		if(!this.isSaved())
		{
			this.data.MULTIPLE = (Text.toBoolean(isMultiple) === true ? 'Y' : 'N');
		}
	}

	isDateField(): boolean
	{
		return (this.getTypeId() === FieldTypes.datetime || this.getTypeId() === FieldTypes.date);
	}

	isShowTime(): boolean
	{
		return (this.getTypeId() === FieldTypes.datetime);
	}

	setIsShowTime(isShowTime)
	{
		if(!this.isSaved())
		{
			isShowTime = Text.toBoolean(isShowTime);
			if(isShowTime)
			{
				this.data.USER_TYPE_ID = FieldTypes.datetime;
			}
			else
			{
				this.data.USER_TYPE_ID = FieldTypes.date;
			}
		}
	}
}