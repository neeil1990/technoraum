import {Loc, Type, Event} from "main.core";

import {CreationMenu} from "./creationmenu";
import {Field} from "./field";
import {MAX_FIELD_LENGTH, DefaultData, DefaultFieldData, FieldDescriptions} from "./fieldtypes";
import {Configurator} from "./configurator";

import 'uf';

export class Factory
{
	constructor(entityId: string, params: {
		creationSignature: string,
		menuId: ?string,
		types: ?Array,
		bindElement: ?Element,
		configuratorClass: ?Configurator,
	} = {})
	{
		this.configuratorClass = Configurator;
		if(Type.isString(entityId) && entityId.length > 0)
		{
			this.entityId = entityId;
		}
		if(Type.isPlainObject(params))
		{
			if(Type.isString(params.creationSignature))
			{
				this.creationSignature = params.creationSignature;
			}
			if(Type.isString(params.menuId))
			{
				this.menuId = params.menuId;
			}
			if(!Type.isArray(params.types))
			{
				params.types = [];
			}
			if(Type.isDomNode(params.bindElement))
			{
				this.bindElement = params.bindElement;
			}
			if(Type.isObject(params.configuratorClass) && params.configuratorClass instanceof Configurator)
			{
				this.configuratorClass = params.configuratorClass;
			}
		}
		else
		{
			params.types = [];
		}
		this.types =  Factory.getFieldTypes().concat(params.types);
	}

	static getFieldTypes(): Array
	{
		const types = [];

		Object.keys(FieldDescriptions).forEach((name) =>
		{
			types.push({...FieldDescriptions[name], ...{name}});
		});

		const eventName = 'UI:UserFieldFactory:Factory:OnGetAdditionalUserTypes';
		const additionalItems = [];
		Event.EventEmitter.emit(eventName, {
			additionalItems
		});
		if(Type.isArray(additionalItems))
		{
			additionalItems.forEach(({name, title, description}) =>
			{
				if(Type.isString(name) && Type.isString(title) && Type.isString(description))
				{
					types.push({name, title, description});
				}
			});
		}

		return types;
	}

	getMenu(params: Object): CreationMenu
	{
		if(!Type.isPlainObject(params))
		{
			params = {};
		}
		if(!Type.isDomNode(params.bindElement))
		{
			params.bindElement = this.bindElement;
		}
		if(!this.menu)
		{
			this.menu = new CreationMenu(this.menuId, this.types, params);
		}

		return this.menu;
	}

	getConfigurator(params: {
		field: Field,
		onSave: Function,
		onCancel: ?Function,
	}): Configurator
	{
		return new this.configuratorClass(params);
	}

	createField(fieldType: string, fieldName: ?string): Field
	{
		let data = {...DefaultData, ...DefaultFieldData[fieldType], ...{USER_TYPE_ID: fieldType}};

		if(!Type.isString(fieldName) || fieldName.length <= 0 || fieldName.length > MAX_FIELD_LENGTH)
		{
			fieldName = this.generateFieldName();
		}
		data.FIELD = fieldName;
		data.ENTITY_ID = this.entityId;
		data.SIGNATURE = this.creationSignature;

		const field = new Field(data);
		field.setTitle(this.getDefaultLabel(fieldType));

		Event.EventEmitter.emit('UI.UserFieldFactory.Factory.onCreateField', {
			factory: this,
			field,
		});

		return field;
	}

	getDefaultLabel(fieldType: string): string
	{
		let label = Loc.getMessage('UI_USERFIELD_FACTORY_UF_LABEL');
		this.types.forEach((type) =>
		{
			if(type.name === fieldType && Type.isString(type.defaultTitle))
			{
				label = type.defaultTitle;
			}
		});

		return label;
	}

	generateFieldName(): string
	{
		let name = 'UF_' + (this.entityId ? (this.entityId + "_") : "");
		let dateSuffix = (new Date()).getTime().toString();
		if(name.length + dateSuffix.length > MAX_FIELD_LENGTH)
		{
			dateSuffix = dateSuffix.substr(((name.length + dateSuffix.length) - MAX_FIELD_LENGTH));
		}

		name += dateSuffix;

		return name;
	}

	saveField(field: Field): Promise<?Field, Array>
	{
		return new Promise((resolve, reject) =>
		{
			if(field instanceof Field)
			{
				if(field.isSaved())
				{
					BX.Main.UF.EditManager.update({ "FIELDS": [field.getData()]}, (response) =>
					{
						this.onFieldSave(response, resolve, reject);
					});
				}
				else
				{
					BX.Main.UF.EditManager.add({ "FIELDS": [field.getData()]}, (response) =>
					{
						this.onFieldSave(response, resolve, reject);
					});
				}
			}
			else
			{
				reject(['Wrong parameter: field must be instance of Field']);
			}
		});
	}

	onFieldSave(response, onSuccess: Function, onError: Function): void
	{
		if(Type.isPlainObject(response))
		{
			if(response.ERROR && Type.isArray(response.ERROR) && response.ERROR.length > 0)
			{
				onError(response.ERROR);
			}
			else
			{
				const fieldData = this.getFieldDataFromResponse(response);
				if(fieldData)
				{
					const field = new Field(fieldData);
					if(Type.isFunction(onSuccess))
					{
						onSuccess(field);
					}
					Event.EventEmitter.emit('UI.UserFieldFactory.Factory.onFieldSave', {
						factory: this,
						field,
					});
				}
			}
		}
		else
		{
			if(Type.isFunction(onError))
			{
				onError(['Error trying save user field settings']);
			}
		}
	}

	getFieldDataFromResponse(response: Object): ?Object
	{
		if(Type.isPlainObject(response))
		{
			let fieldData;
			Object.keys(response).forEach((fieldName) =>
			{
				if(Type.isPlainObject(response[fieldName]['FIELD']))
				{
					fieldData = response[fieldName]['FIELD'];
				}
			});

			return fieldData;
		}

		return null;
	}
}