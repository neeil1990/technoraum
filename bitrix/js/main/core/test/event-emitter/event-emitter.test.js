import {Event, BaseError} from '../../src/core';
import BX from '../old/core/internal/bootstrap';

describe('Event.EventEmitter', () => {
	it('Should be exported as function', () => {
		assert(typeof Event.EventEmitter === 'function');
	});

	it('Should implement public interface', () => {
		const emitter = new Event.EventEmitter();

		assert(typeof emitter.subscribe === 'function');
		assert(typeof emitter.subscribeOnce === 'function');
		assert(typeof emitter.emit === 'function');
		assert(typeof emitter.unsubscribe === 'function');
		assert(typeof emitter.getMaxListeners === 'function');
		assert(typeof emitter.setMaxListeners === 'function');
		assert(typeof emitter.getListeners === 'function');
	});

	describe('subscribe', () => {
		it('Should add event listener', () => {
			const emitter = new Event.EventEmitter();
			const event = 'test:event';
			const listener1 = () => {};
			const listener2 = () => {};
			const listener3 = () => {};

			emitter.subscribe(event, listener1);
			emitter.subscribe(event, listener2);
			emitter.subscribe(event, listener3);

			assert.equal(emitter.getListeners(event).size, 3);
		});

		it('Should add unique listeners only', () => {

			const emitter = new Event.EventEmitter();
			const event = 'test:event';
			const listener = () => {};
			const listener2 = () => {};
			const consoleError = sinon.spy(console, 'error');

			emitter.subscribe(event, listener);
			emitter.subscribe(event, listener);
			emitter.subscribe(event, listener);
			emitter.subscribe(event, listener);

			assert(consoleError.callCount === 3);

			emitter.subscribeOnce(event, listener);
			emitter.subscribeOnce(event, listener);
			emitter.subscribeOnce(event, listener);

			assert(consoleError.callCount === 6);

			emitter.subscribeOnce(event, listener2);
			emitter.subscribeOnce(event, listener2);
			emitter.subscribeOnce(event, listener2);
			emitter.subscribe(event, listener2);
			emitter.subscribe(event, listener2);

			assert(consoleError.callCount === 10);

			consoleError.restore();

			assert.equal(emitter.getListeners(event).size, 2);

			const obj = {};
			const once = sinon.stub();
			Event.EventEmitter.subscribeOnce(obj, 'event:once', once);
			Event.EventEmitter.emit(obj, 'event:once');
			Event.EventEmitter.emit(obj, 'event:once');
			Event.EventEmitter.emit(obj, 'event:once');

			assert.equal(once.callCount, 1);
		});
	});

	describe('unsubscribe', () => {
		it('Should remove specified event listener', () => {
			const emitter = new Event.EventEmitter();
			const event = 'test:event';
			const listener1 = () => {};
			const listener2 = () => {};
			const listener3 = () => {};

			emitter.subscribe(event, listener1);
			emitter.subscribe(event, listener2);
			emitter.subscribe(event, listener3);

			emitter.unsubscribe(event, listener1);

			assert.equal(emitter.getListeners(event).size, 2);
			assert(emitter.getListeners(event).has(listener1) === false);
			assert(emitter.getListeners(event).has(listener2) === true);
			assert(emitter.getListeners(event).has(listener3) === true);
		});
	});

	describe('unsubscribeAll', () => {
		it('Should unsubscribe event listeners', () => {
			const emitter = new Event.EventEmitter();
			const eventName = 'test:event';
			const listener1 = () => {};
			const listener2 = () => {};
			const listener3 = () => {};
			const listener4 = () => {};

			emitter.subscribe(eventName, listener1);
			emitter.subscribe(eventName, listener2);
			emitter.subscribe(eventName, listener3);
			emitter.subscribe(eventName + "2", listener4);

			assert.equal(emitter.getListeners(eventName).size, 3);

			emitter.unsubscribeAll(eventName);

			assert.equal(emitter.getListeners(eventName).size, 0);
		});

		it('Should unsubscribe all event listeners', () => {
			const emitter = new Event.EventEmitter();
			const eventName = 'test:event';
			const eventName2 = 'test:event2';
			const listener1 = () => {};
			const listener2 = () => {};
			const listener3 = () => {};
			const listener4 = () => {};

			emitter.subscribe(eventName, listener1);
			emitter.subscribe(eventName, listener2);
			emitter.subscribe(eventName, listener3);

			emitter.subscribe(eventName2, listener1);
			emitter.subscribe(eventName2, listener2);
			emitter.subscribe(eventName2, listener3);
			emitter.subscribe(eventName2, listener4);

			assert.equal(emitter.getListeners(eventName).size, 3);
			assert.equal(emitter.getListeners(eventName2).size, 4);

			emitter.unsubscribeAll();

			assert.equal(emitter.getListeners(eventName).size, 0);
			assert.equal(emitter.getListeners(eventName2).size, 0);
		});

	});

	describe('emit', () => {
		it('Should call all event listeners', () => {
			const emitter = new Event.EventEmitter();
			const event = 'test:event';
			const listener1 = sinon.stub();
			const listener2 = sinon.stub();
			const listener3 = sinon.stub();

			emitter.subscribe(event, listener1);
			emitter.subscribe(event, listener2);
			emitter.subscribe(event, listener3);

			emitter.emit(event);

			assert(listener1.calledOnce);
			assert(listener2.calledOnce);
			assert(listener3.calledOnce);
		});

		it('Should not call listener if was unsubscribe by a previous sibling listener', () => {
			const emitter = new Event.EventEmitter();
			const eventName = 'event:sibling';

			let result = '';
			const listener1 = () => { result += "1"; };
			const listener2 = () => { result += "2"; emitter.unsubscribe(eventName, listener3)};
			const listener3 = () => { result += "3"; };

			emitter.subscribe(eventName, listener1);
			emitter.subscribe(eventName, listener2);
			emitter.subscribe(eventName, listener3);

			emitter.emit(eventName);

			assert.equal(result, '12');
		});

		it('Should execute listeners in a right sequence.', () => {
			let result = '';
			const listener1 = () => { result += "1"; };
			const listener2 = () => { result += "2"; };
			const listener3 = () => { result += "3"; };
			const listener4 = () => { result += "4"; };
			const listener5 = () => { result += "5"; };

			const emitter = new Event.EventEmitter();
			const eventName = 'event:sequence';

			emitter.subscribe(eventName, listener1);
			Event.EventEmitter.subscribe(Event.EventEmitter.GLOBAL_TARGET, eventName, listener2);
			emitter.subscribe(eventName, listener3);
			Event.EventEmitter.subscribe(Event.EventEmitter.GLOBAL_TARGET, eventName, listener4);
			emitter.subscribe(eventName, listener5);

			emitter.emit(eventName);

			assert.equal(result, '12345');
		});

		it('Should call event listeners after each emit call', () => {
			const emitter = new Event.EventEmitter();
			const event = 'test:event';
			const listener1 = sinon.stub();
			const listener2 = sinon.stub();
			const listener3 = sinon.stub();

			emitter.subscribe(event, listener1);
			emitter.subscribe(event, listener2);
			emitter.subscribe(event, listener3);

			emitter.emit(event);

			assert(listener1.callCount === 1);
			assert(listener2.callCount === 1);
			assert(listener3.callCount === 1);

			emitter.emit(event);

			assert(listener1.callCount === 2);
			assert(listener2.callCount === 2);
			assert(listener3.callCount === 2);

			emitter.emit(event);
			emitter.emit(event);
			emitter.emit(event);

			assert(listener1.callCount === 5);
			assert(listener2.callCount === 5);
			assert(listener3.callCount === 5);
		});

		it('Should not call deleted listeners', () => {
			const emitter = new Event.EventEmitter();
			const event = 'test:event';
			const listener1 = sinon.stub();
			const listener2 = sinon.stub();
			const listener3 = sinon.stub();

			emitter.subscribe(event, listener1);
			emitter.subscribe(event, listener2);
			emitter.subscribe(event, listener3);

			emitter.emit(event);

			assert(listener1.callCount === 1);
			assert(listener2.callCount === 1);
			assert(listener3.callCount === 1);

			emitter.unsubscribe(event, listener1);
			emitter.emit(event);

			assert(listener1.callCount === 1);
			assert(listener2.callCount === 2);
			assert(listener3.callCount === 2);
		});

		it('Should call listener with valid Event object anyway', async () => {
			const emitter = new Event.EventEmitter();
			const eventName = "test:event";

			await new Promise((resolve) => {
				emitter.subscribe(eventName, (event) => {
					assert(event instanceof Event.BaseEvent);
					assert(event.type === eventName);
					assert(event.hasOwnProperty("data"));
					assert(event.defaultPrevented === false);
					assert(event.immediatePropagationStopped === false);
					assert(typeof event.preventDefault === 'function');
					assert(typeof event.stopImmediatePropagation === 'function');
					assert(typeof event.isImmediatePropagationStopped === 'function');
					resolve();
				});
				emitter.emit(eventName);
			});
		});

		it('Should assign props to data if passed plain object', async () => {
			const emitter = new Event.EventEmitter();
			const eventName = "Test:event";

			await new Promise((resolve) => {
				emitter.subscribe(eventName, (event) => {
					assert(event.data.test1 === 1);
					assert(event.data.test2 === 2);
					resolve();
				});
				emitter.emit(eventName, {test1: 1, test2: 2});
			});
		});

		it('Should add event value to data.event.value if passed not event object and not plain object', async () => {
			const emitter = new Event.EventEmitter();
			const eventName = "Test:event";

			await new Promise((resolve) => {
				emitter.subscribe(eventName, (event) => {
					assert(Array.isArray(event.data));
					assert(event.data[0] === 1);
					assert(event.data[1] === 2);
					resolve();
				});
				emitter.emit(eventName, [1, 2]);
			});

			await new Promise((resolve) => {
				emitter.subscribe(`${eventName}2`, (event) => {
					assert(typeof event.data === 'string');
					assert(event.data === 'test');
					resolve();
				});
				emitter.emit(`${eventName}2`, 'test');
			});

			await new Promise((resolve) => {
				emitter.subscribe(`${eventName}3`, (event) => {
					assert(typeof event.data === 'boolean');
					assert(event.data === true);
					resolve();
				});
				emitter.emit(`${eventName}3`, true);
			});
		});

/*
		it('Should set event.isTrusted = true if event emitted with instance method', async () => {
			class Emitter extends Event.EventEmitter {}
			const emitter = new Emitter();

			await new Promise((resolve) => {
				emitter.subscribe("test", (event) => {
					assert(event.isTrusted === true);
					resolve();
				});
				emitter.emit("test");
			});
		});

		it('Should set event.isTrusted = false if event emitted with static method', async () => {
			class Emitter extends Event.EventEmitter {}
			const emitter = new Emitter();

			await new Promise((resolve) => {
				emitter.subscribe("test2", (event) => {
					assert(event.isTrusted === false);
					resolve();
				});
				Event.EventEmitter.emit("test2");
			});

			await new Promise((resolve) => {
				emitter.subscribe("test3", (event) => {
					assert(event.isTrusted === false);
					resolve();
				});
				Emitter.emit("test3");
			});
		});
*/

		it('Should set defaultPrevented = true called .preventDefault() in listener', async () => {
			const emitter = new Event.EventEmitter();

			emitter.subscribe('test4', (event) => {
				event.preventDefault();
			});

			const event = new Event.BaseEvent();

			emitter.emit('test4', event);

			assert(event.isDefaultPrevented() === true);
			assert(event.defaultPrevented === true);
		});

		it('Should set thisArg for listeners', (done) => {

			const eventName = 'My:EventName';
			const obj = {};
			const thisArg = { a: 1 };

			BX.addCustomEvent(eventName, function() {
				assert.equal(this, thisArg);
			});

			Event.EventEmitter.subscribe(eventName, function() {
				assert.equal(this, thisArg);
				done();
			});

			Event.EventEmitter.emit(obj, eventName, {}, { thisArg });
		});
	});

	describe('emitAsync', () => {
		it('Should emit event and return promise', () => {
			const emitter = new Event.EventEmitter();
			const resultPromise = emitter.emitAsync('test');

			assert.ok(resultPromise instanceof Promise);
		});

		it('Should resolve returned promise with values that returned from listeners', () => {
			const emitter = new Event.EventEmitter();

			emitter.subscribe('test', () => {
				return 'result-1';
			});

			emitter.subscribe('test', () => {
				return true;
			});

			emitter.subscribe('test', () => {
				return 'test-result-3';
			});

			return emitter
				.emitAsync('test')
				.then((results) => {
					assert.ok(results[0] === 'result-1');
					assert.ok(results[1] === true);
					assert.ok(results[2] === 'test-result-3');
				});
		});

		it('Promise should be resolved, when resolved all promises returned from listeners', () => {
			const emitter = new Event.EventEmitter();

			emitter.subscribe('test', () => {
				return new Promise((resolve) => {
					setTimeout(() => {
						resolve('value1');
					}, 500);
				});
			});

			emitter.subscribe('test', () => {
				return new Promise((resolve) => {
					setTimeout(() => {
						resolve('value2');
					}, 700);
				});
			});

			emitter.subscribe('test', () => {
				return new Promise((resolve) => {
					setTimeout(() => {
						resolve('value3');
					}, 900);
				});
			});

			return emitter
				.emitAsync('test')
				.then((results) => {
					assert.ok(results[0] === 'value1');
					assert.ok(results[1] === 'value2');
					assert.ok(results[2] === 'value3');
				});
		});

		it('Should reject returned promise if listener throw error', () => {
			const emitter = new Event.EventEmitter();

			emitter.subscribe('test', () => {
				return Promise.reject(new Error());
			});

			emitter
				.emitAsync('test')
				.then(() => {})
				.catch((err) => {
					assert.ok(err instanceof Error);
				});
		});
	});

	describe('static emitAsync', () => {
		it('Should emit event and return promise', () => {
			const resultPromise = Event.EventEmitter.emitAsync('test-event--1');
			assert.ok(resultPromise instanceof Promise);
		});

		it('Should resolve returned promise with values that returned from listeners', () => {
			const emitter = new Event.EventEmitter();

			emitter.subscribe('test-event-1', () => {
				return 'result-1';
			});

			emitter.subscribe('test-event-1', () => {
				return true;
			});

			emitter.subscribe('test-event-1', () => {
				return 'test-result-3';
			});

			return Event.EventEmitter
				.emitAsync(emitter, 'test-event-1')
				.then((results) => {
					assert.ok(results[0] === 'result-1');
					assert.ok(results[1] === true);
					assert.ok(results[2] === 'test-result-3');
				});
		});

		it('Promise should be resolved, when resolved all promises returned from listeners', () => {
			const emitter = new Event.EventEmitter();

			emitter.subscribe('test-event-2', () => {
				return new Promise((resolve) => {
					setTimeout(() => {
						resolve('value1');
					}, 500);
				});
			});

			emitter.subscribe('test-event-2', () => {
				return new Promise((resolve) => {
					setTimeout(() => {
						resolve('value2');
					}, 700);
				});
			});

			emitter.subscribe('test-event-2', () => {
				return new Promise((resolve) => {
					setTimeout(() => {
						resolve('value3');
					}, 900);
				});
			});

			return Event.EventEmitter
				.emitAsync(emitter, 'test-event-2')
				.then((results) => {
					assert.ok(results[0] === 'value1');
					assert.ok(results[1] === 'value2');
					assert.ok(results[2] === 'value3');
				});
		});

		it('Should reject returned promise if listener throw error', () => {
			const emitter = new Event.EventEmitter();

			emitter.subscribe('test-event-3', () => {
				return Promise.reject(new Error());
			});

			return Event.EventEmitter
				.emitAsync(emitter, 'test-event-3')
				.then(() => {})
				.catch((err) => {
					assert.ok(err instanceof Error);
				});
		});
	});

	describe('subscribeOnce', () => {
		it('Should call listener only once', () => {
			const emitter = new Event.EventEmitter();
			const event = 'test:event';
			const listener = sinon.stub();

			emitter.subscribeOnce(event, listener);
			emitter.emit(event);
			emitter.emit(event);
			emitter.emit(event);
			emitter.emit(event);

			assert(listener.calledOnce);
		});

		it('Should add only unique listeners', () => {
			const emitter = new Event.EventEmitter();
			const event = 'test:event';
			const listener = sinon.stub();

			emitter.subscribeOnce(event, listener);
			emitter.subscribeOnce(event, listener);
			emitter.subscribeOnce(event, listener);
			emitter.subscribeOnce(event, listener);

			emitter.emit(event);
			emitter.emit(event);
			emitter.emit(event);
			emitter.emit(event);

			assert(listener.calledOnce);
		});
	});

	describe('setMaxListeners', () => {
		it('Should set max allowed listeners count', () => {
			const emitter = new Event.EventEmitter();
			const maxListenersCount = 3;

			emitter.setMaxListeners(maxListenersCount);
			emitter.setMaxListeners('onClose', 5);

			assert(emitter.getMaxListeners() === maxListenersCount);
			assert(emitter.getMaxListeners('onXXX') === maxListenersCount);
			assert(emitter.getMaxListeners('onClose') === 5);
		});

		it('Should set max listeners count for the event', () => {
			const emitter = new Event.EventEmitter();
			const eventName = "MyEventMaxListeners";
			const maxListenersCount = 3;

			emitter.setMaxListeners(eventName, maxListenersCount);

			assert(emitter.getMaxListeners() === Event.EventEmitter.DEFAULT_MAX_LISTENERS);
			assert(emitter.getMaxListeners(eventName) === maxListenersCount);

			assert(Event.EventEmitter.getMaxListeners({}) === Event.EventEmitter.DEFAULT_MAX_LISTENERS);
			assert(Event.EventEmitter.getMaxListeners({}, eventName) === Event.EventEmitter.DEFAULT_MAX_LISTENERS);
		});

		it('Should print warnings if the limit exceeded', () => {
			const obj = {};
			const eventName = "limit-subscribers";
			const eventName2 = "limit-subscribers2";
			const listener1 = sinon.stub();
			const listener2 = sinon.stub();
			const listener3 = sinon.stub();
			const listener4 = sinon.stub();

			Event.EventEmitter.setMaxListeners(obj, eventName, 2);
			assert(Event.EventEmitter.getMaxListeners(obj) === Event.EventEmitter.DEFAULT_MAX_LISTENERS);
			assert(Event.EventEmitter.getMaxListeners(obj, eventName) === 2);

			Event.EventEmitter.subscribe(obj, eventName, listener1);
			Event.EventEmitter.subscribe(obj, eventName, listener2);
			Event.EventEmitter.subscribe(obj, eventName, listener3);
			Event.EventEmitter.subscribe(obj, eventName, listener4);

			Event.EventEmitter.emit(obj, eventName);

			Event.EventEmitter.subscribe(obj, eventName2, listener1);
			Event.EventEmitter.subscribe(obj, eventName2, listener2);
			Event.EventEmitter.subscribe(obj, eventName2, listener3);
			Event.EventEmitter.subscribe(obj, eventName2, listener4);

			Event.EventEmitter.emit(obj, eventName2);

		});
		it('Should sets max listeners for global target', () => {
			const obj = {};

			assert.equal(Event.EventEmitter.getMaxListeners(), 25);
			assert.equal(Event.EventEmitter.getMaxListeners(obj), Event.EventEmitter.DEFAULT_MAX_LISTENERS);

			Event.EventEmitter.setMaxListeners(55);
			Event.EventEmitter.setMaxListeners('onMyClick', 77);

			assert.equal(Event.EventEmitter.getMaxListeners(), 55);
			assert.equal(Event.EventEmitter.getMaxListeners(obj), Event.EventEmitter.DEFAULT_MAX_LISTENERS);
			assert.equal(Event.EventEmitter.getMaxListeners('onMyClick'), 77);
			assert.equal(Event.EventEmitter.getMaxListeners(obj, 'onMyClick'), Event.EventEmitter.DEFAULT_MAX_LISTENERS);

			Event.EventEmitter.setMaxListeners(obj, 88);
			Event.EventEmitter.setMaxListeners(obj, 'onMyClick', 99);

			assert.equal(Event.EventEmitter.getMaxListeners(), 55);
			assert.equal(Event.EventEmitter.getMaxListeners('onMyClick'), 77);
			assert.equal(Event.EventEmitter.getMaxListeners(obj), 88);
			assert.equal(Event.EventEmitter.getMaxListeners(obj, 'onMyClick'), 99);
			assert.equal(Event.EventEmitter.getMaxListeners(obj, 'onXXX'), 88);
		});
	});

	describe('incrementMaxListeners/decrementMaxListeners', () => {

		it('Should increment/decrement events for the global target', () => {

			const obj = {};
			const eventName = 'onMySpecialEvent';

			const defaultGlobalMaxListeners = Event.EventEmitter.getMaxListeners();
			assert.equal(Event.EventEmitter.getMaxListeners(), defaultGlobalMaxListeners);
			assert.equal(Event.EventEmitter.getMaxListeners(eventName), defaultGlobalMaxListeners);
			assert.equal(Event.EventEmitter.getMaxListeners(obj), Event.EventEmitter.DEFAULT_MAX_LISTENERS);

			Event.EventEmitter.incrementMaxListeners();
			Event.EventEmitter.incrementMaxListeners();
			Event.EventEmitter.incrementMaxListeners();
			Event.EventEmitter.setMaxListeners(eventName, defaultGlobalMaxListeners);

			assert.equal(Event.EventEmitter.getMaxListeners(), defaultGlobalMaxListeners + 3);
			assert.equal(Event.EventEmitter.getMaxListeners(eventName), defaultGlobalMaxListeners);
			assert.equal(Event.EventEmitter.getMaxListeners(obj), Event.EventEmitter.DEFAULT_MAX_LISTENERS);

			Event.EventEmitter.incrementMaxListeners();
			Event.EventEmitter.incrementMaxListeners();
			Event.EventEmitter.incrementMaxListeners(eventName);
			Event.EventEmitter.incrementMaxListeners(eventName);

			assert.equal(Event.EventEmitter.getMaxListeners(), defaultGlobalMaxListeners + 5);
			assert.equal(Event.EventEmitter.getMaxListeners(eventName), defaultGlobalMaxListeners + 2);
			assert.equal(Event.EventEmitter.getMaxListeners(obj), Event.EventEmitter.DEFAULT_MAX_LISTENERS);

			Event.EventEmitter.incrementMaxListeners(3);
			Event.EventEmitter.incrementMaxListeners(eventName);
			Event.EventEmitter.incrementMaxListeners(eventName, 4);

			assert.equal(Event.EventEmitter.getMaxListeners(), defaultGlobalMaxListeners + 8);
			assert.equal(Event.EventEmitter.getMaxListeners(eventName), defaultGlobalMaxListeners + 7);
			assert.equal(Event.EventEmitter.getMaxListeners(obj), Event.EventEmitter.DEFAULT_MAX_LISTENERS);

			Event.EventEmitter.incrementMaxListeners(obj, eventName);
			Event.EventEmitter.incrementMaxListeners(obj, eventName);
			Event.EventEmitter.incrementMaxListeners(obj, eventName, 7);
			Event.EventEmitter.incrementMaxListeners(obj);
			Event.EventEmitter.incrementMaxListeners(obj);
			Event.EventEmitter.incrementMaxListeners(obj, 3);

			assert.equal(Event.EventEmitter.getMaxListeners(), defaultGlobalMaxListeners + 8);
			assert.equal(Event.EventEmitter.getMaxListeners(eventName), defaultGlobalMaxListeners + 7);
			assert.equal(Event.EventEmitter.getMaxListeners(obj), Event.EventEmitter.DEFAULT_MAX_LISTENERS + 5);
			assert.equal(Event.EventEmitter.getMaxListeners(obj, eventName), Event.EventEmitter.DEFAULT_MAX_LISTENERS + 9);

			Event.EventEmitter.decrementMaxListeners(obj, eventName);
			Event.EventEmitter.decrementMaxListeners(obj, eventName, 7);
			Event.EventEmitter.decrementMaxListeners(obj);
			Event.EventEmitter.decrementMaxListeners(obj, 3);

			assert.equal(Event.EventEmitter.getMaxListeners(obj, eventName), Event.EventEmitter.DEFAULT_MAX_LISTENERS + 1);
			assert.equal(Event.EventEmitter.getMaxListeners(obj), Event.EventEmitter.DEFAULT_MAX_LISTENERS + 1);
			assert.equal(Event.EventEmitter.getMaxListeners(), defaultGlobalMaxListeners + 8);
			assert.equal(Event.EventEmitter.getMaxListeners(eventName), defaultGlobalMaxListeners + 7);

			Event.EventEmitter.decrementMaxListeners(3);
			Event.EventEmitter.decrementMaxListeners(eventName);
			Event.EventEmitter.decrementMaxListeners(eventName, 4);

			assert.equal(Event.EventEmitter.getMaxListeners(obj, eventName), Event.EventEmitter.DEFAULT_MAX_LISTENERS + 1);
			assert.equal(Event.EventEmitter.getMaxListeners(obj), Event.EventEmitter.DEFAULT_MAX_LISTENERS + 1);
			assert.equal(Event.EventEmitter.getMaxListeners(), defaultGlobalMaxListeners + 5);
			assert.equal(Event.EventEmitter.getMaxListeners(eventName), defaultGlobalMaxListeners + 2);
		});

		it('Should increment events for an object target', () => {

			const emitter = new Event.EventEmitter();
			const eventName = 'onMyEmitterEvent';

			assert.equal(emitter.getMaxListeners(), Event.EventEmitter.DEFAULT_MAX_LISTENERS);
			assert.equal(emitter.getMaxListeners(eventName), Event.EventEmitter.DEFAULT_MAX_LISTENERS);

			emitter.incrementMaxListeners();
			emitter.incrementMaxListeners();
			emitter.incrementMaxListeners(3);
			emitter.setMaxListeners(eventName, 30);

			assert.equal(emitter.getMaxListeners(), Event.EventEmitter.DEFAULT_MAX_LISTENERS + 5);
			assert.equal(emitter.getMaxListeners(eventName), 30);

			emitter.incrementMaxListeners(eventName);
			emitter.incrementMaxListeners(eventName);
			emitter.incrementMaxListeners(eventName, 3);

			assert.equal(emitter.getMaxListeners(), Event.EventEmitter.DEFAULT_MAX_LISTENERS + 5);
			assert.equal(emitter.getMaxListeners(eventName), 35);

			emitter.decrementMaxListeners();
			emitter.decrementMaxListeners(3);
			emitter.decrementMaxListeners(eventName);
			emitter.decrementMaxListeners(eventName, 2);

			assert.equal(emitter.getMaxListeners(), Event.EventEmitter.DEFAULT_MAX_LISTENERS + 1);
			assert.equal(emitter.getMaxListeners(eventName), 32);
		});

	});

	describe('getMaxListeners', () => {
		it('Should return max listeners count for each event', () => {
			const emitter = new Event.EventEmitter();
			const defaultMaxListenersCount = 10;

			assert(emitter.getMaxListeners() === defaultMaxListenersCount);
		});
	});

	describe('static', () => {
		it('Should implement public static interface', () => {
			assert(typeof Event.EventEmitter.subscribe === 'function');
			assert(typeof Event.EventEmitter.subscribeOnce === 'function');
			assert(typeof Event.EventEmitter.emit === 'function');
			assert(typeof Event.EventEmitter.unsubscribe === 'function');
			assert(typeof Event.EventEmitter.getMaxListeners === 'function');
			assert(typeof Event.EventEmitter.setMaxListeners === 'function');
			assert(typeof Event.EventEmitter.getListeners === 'function');
		});

		it('Should add global event listener', () => {
			const emitter = new Event.EventEmitter();
			const eventName = 'test:event';
			const listener = sinon.stub();

			Event.EventEmitter.subscribe(emitter, eventName, listener);

			emitter.emit(eventName);

			assert(listener.callCount === 1);

			emitter.emit(eventName);
			emitter.emit(eventName);

			assert(listener.callCount === 3);
		});
	});

	describe('Old custom events', () => {
		it('Should implement public static interface', () => {
			assert(typeof BX.addCustomEvent === 'function');
			assert(typeof BX.onCustomEvent === 'function');
			assert(typeof BX.removeCustomEvent === 'function');
			assert(typeof BX.removeAllCustomEvents === 'function');
		});

		it('Should add an event listener', () => {

			const obj = {};
			const eventName = 'old:add-custom-event';
			const listener1 = sinon.stub();
			const listener2 = sinon.stub();
			const listener3 = sinon.stub();

			BX.addCustomEvent(obj, eventName, listener1);
			BX.addCustomEvent(obj, eventName, listener2);
			BX.addCustomEvent(obj, eventName, listener3);

			assert.equal(Event.EventEmitter.getListeners(obj, eventName).size, 3);

			BX.onCustomEvent(obj, eventName);

			assert(listener1.calledOnce);
			assert(listener2.calledOnce);
			assert(listener3.calledOnce);
		});

		it('Should add global listeners', () => {

			const obj = {};
			const eventName = 'old:add-custom-event';
			const listener1 = sinon.stub();
			const listener2 = sinon.stub();
			const listener3 = sinon.stub();
			const listener4 = sinon.stub();

			BX.addCustomEvent(window, eventName, listener1);
			BX.addCustomEvent(eventName, listener2);
			BX.addCustomEvent(Event.EventEmitter.GLOBAL_TARGET, eventName, listener3);
			BX.addCustomEvent(obj, eventName, listener4);

			assert.equal(Event.EventEmitter.getListeners(Event.EventEmitter.GLOBAL_TARGET, eventName).size, 3);
			assert.equal(Event.EventEmitter.getListeners(eventName).size, 3);

			BX.onCustomEvent(window, eventName);

			assert(listener1.callCount === 1);
			assert(listener2.callCount === 1);
			assert(listener3.callCount === 1);

			BX.onCustomEvent(eventName);

			assert(listener1.callCount === 2);
			assert(listener2.callCount === 2);
			assert(listener3.callCount === 2);

			BX.onCustomEvent(obj, eventName);

			assert(listener1.callCount === 3);
			assert(listener2.callCount === 3);
			assert(listener3.callCount === 3);
			assert(listener4.callCount === 1);
		});

		it('Should invoke event listeners', () => {

			const obj = {};
			const eventName = 'test:event';
			const listener = sinon.stub();

			BX.addCustomEvent(obj, eventName, listener);

			BX.onCustomEvent(obj, eventName);

			assert(listener.callCount === 1);

			BX.onCustomEvent(obj, eventName);
			BX.onCustomEvent(obj, eventName);

			assert(listener.callCount === 3);
		});

		it('Should pass arguments', (done) => {

			const obj = {};
			const eventName = 'test:event';

			const listener = function(a, b, c) {

				assert.equal(a, 1);
				assert.equal(b, obj);
				assert.equal(c, "string");

				done();
			};

			BX.addCustomEvent(obj, eventName, listener);
			BX.onCustomEvent(obj, eventName, [1, obj, "string"]);
		});

		it('Should pass array-like arguments', (done) => {

			const obj = {};
			const eventName = 'test:onChanged';

			const listener = function(a, b, c) {

				assert.equal(a, 1);
				assert.equal(b, obj);
				assert.equal(c, "string");

				done();
			};

			function fireEvent()
			{
				BX.onCustomEvent(obj, eventName, arguments);
			}

			BX.addCustomEvent(obj, eventName, listener);
			fireEvent(1, obj, "string");
		});

		it('Should emit params for old handlers', (done) => {

			const emitter = new Event.EventEmitter();
			const eventName = 'onPopupClose';
			const listener = (a, b, c) => {

				assert.equal(a, 1);
				assert.equal(b, emitter);
				assert.equal(c, "string");

				done();
			};

			BX.addCustomEvent(emitter, eventName, listener);

			const event = new Event.BaseEvent();
			event.setCompatData([1, emitter, "string"]);

			emitter.emit(eventName, event);
		});

		it('Should emit an event for new handlers', (done) => {

			const emitter = new Event.EventEmitter();
			const eventName = 'onPopupClose';
			const listener = function(event) {
				assert.equal(event.getData(), 2);
				done();
			};

			BX.addCustomEvent(emitter, eventName, listener);

			emitter.emit(eventName, 2);
		});

		it('Should emit an event for new subscribers', (done) => {

			const obj = {};
			const eventName = 'test:event';

			Event.EventEmitter.subscribe(obj, eventName, (event) => {

				const [num, instance, str] = event.getData();

				assert.equal(num, 1);
				assert.equal(instance, obj);
				assert.equal(str, "string");

				done();
			});

			BX.onCustomEvent(obj, eventName, [1, obj, "string"]);
		});
	});

	describe('StopImmediatePropagation', () => {
		it('Should stop invoke the rest listeners', () => {

			const emitter = new Event.EventEmitter();
			const eventName = 'event:stop-propagation';
			const listener1 = sinon.stub();
			const listener2 = (event) => {
				event.stopImmediatePropagation();
			};
			const listener3 = sinon.stub();

			emitter.subscribe(eventName, listener1);
			emitter.subscribe(eventName, listener2);
			emitter.subscribe(eventName, listener3);

			emitter.emit(eventName);

			assert(listener1.callCount === 1);
			assert(listener3.callCount === 0);
		});
	});

	describe('Global Context', () => {
		it('Should add event listeners', () => {
			const eventName = 'event:global-context';
			const listener1 = () => {};
			const listener2 = () => {};
			const listener3 = () => {};

			Event.EventEmitter.subscribe(eventName, listener1);
			Event.EventEmitter.subscribe(eventName, listener2);
			Event.EventEmitter.subscribe(eventName, listener3);

			assert.equal(Event.EventEmitter.getListeners(eventName).size, 3);
			assert.equal(Event.EventEmitter.getListeners(Event.EventEmitter.GLOBAL_TARGET, eventName).size, 3);
		});

		it('Should remove specified event listener', () => {
			const eventName = 'event:global-context-unsubscribe';
			const listener1 = () => {};
			const listener2 = () => {};
			const listener3 = () => {};
			const listener4 = () => {};

			Event.EventEmitter.subscribe(eventName, listener1);
			Event.EventEmitter.subscribe(Event.EventEmitter.GLOBAL_TARGET, eventName, listener2);
			Event.EventEmitter.subscribe(eventName, listener3);
			Event.EventEmitter.subscribe(Event.EventEmitter.GLOBAL_TARGET, eventName, listener4);

			assert.equal(Event.EventEmitter.getListeners(eventName).size, 4);
			assert.equal(Event.EventEmitter.getListeners(Event.EventEmitter.GLOBAL_TARGET, eventName).size, 4);

			Event.EventEmitter.unsubscribe(eventName, listener1);
			Event.EventEmitter.unsubscribe(Event.EventEmitter.GLOBAL_TARGET, eventName, listener3);

			assert.equal(Event.EventEmitter.getListeners(eventName).size, 2);
			assert.equal(Event.EventEmitter.getListeners(Event.EventEmitter.GLOBAL_TARGET, eventName).size, 2);

			assert(Event.EventEmitter.getListeners(eventName).has(listener1) === false);
			assert(Event.EventEmitter.getListeners(Event.EventEmitter.GLOBAL_TARGET, eventName).has(listener1) === false);

			assert(Event.EventEmitter.getListeners(eventName).has(listener2) === true);
			assert(Event.EventEmitter.getListeners(Event.EventEmitter.GLOBAL_TARGET, eventName).has(listener2) === true);

			assert(Event.EventEmitter.getListeners(eventName).has(listener3) === false);
			assert(Event.EventEmitter.getListeners(Event.EventEmitter.GLOBAL_TARGET, eventName).has(listener3) === false);

			assert(Event.EventEmitter.getListeners(eventName).has(listener4) === true);
			assert(Event.EventEmitter.getListeners(Event.EventEmitter.GLOBAL_TARGET, eventName).has(listener4) === true);
		});

		it('Should remove all event listeners', () => {
			const eventName = 'event:global-context-unsubscribe-all';
			const eventName2 = 'event:global-context-unsubscribe-all2';
			const listener1 = () => {};
			const listener2 = () => {};
			const listener3 = () => {};
			const listener4 = () => {};

			Event.EventEmitter.subscribe(eventName, listener1);
			Event.EventEmitter.subscribe(Event.EventEmitter.GLOBAL_TARGET, eventName, listener2);
			Event.EventEmitter.subscribe(eventName, listener3);
			Event.EventEmitter.subscribe(Event.EventEmitter.GLOBAL_TARGET, eventName, listener4);

			Event.EventEmitter.subscribe(eventName2, listener1);
			Event.EventEmitter.subscribe(Event.EventEmitter.GLOBAL_TARGET, eventName2, listener2);
			Event.EventEmitter.subscribe(eventName2, listener3);

			assert.equal(Event.EventEmitter.getListeners(eventName).size, 4);
			assert.equal(Event.EventEmitter.getListeners(Event.EventEmitter.GLOBAL_TARGET, eventName).size, 4);

			assert.equal(Event.EventEmitter.getListeners(eventName2).size, 3);
			assert.equal(Event.EventEmitter.getListeners(Event.EventEmitter.GLOBAL_TARGET, eventName2).size, 3);

			Event.EventEmitter.unsubscribeAll(eventName);

			assert.equal(Event.EventEmitter.getListeners(eventName).size, 0);
			assert.equal(Event.EventEmitter.getListeners(Event.EventEmitter.GLOBAL_TARGET, eventName).size, 0);

			assert.equal(Event.EventEmitter.getListeners(eventName2).size, 3);
			assert.equal(Event.EventEmitter.getListeners(Event.EventEmitter.GLOBAL_TARGET, eventName2).size, 3);

			Event.EventEmitter.unsubscribeAll(eventName2);

			assert.equal(Event.EventEmitter.getListeners(eventName).size, 0);
			assert.equal(Event.EventEmitter.getListeners(Event.EventEmitter.GLOBAL_TARGET, eventName).size, 0);

			assert.equal(Event.EventEmitter.getListeners(eventName2).size, 0);
			assert.equal(Event.EventEmitter.getListeners(Event.EventEmitter.GLOBAL_TARGET, eventName2).size, 0);
		});

		it('setMaxListeners', () => {

			Event.EventEmitter.setMaxListeners(111);

			assert.equal(Event.EventEmitter.getMaxListeners(), 111);
			assert.equal(Event.EventEmitter.getMaxListeners(Event.EventEmitter.GLOBAL_TARGET), 111);

			Event.EventEmitter.setMaxListeners(Event.EventEmitter.GLOBAL_TARGET, 222);

			assert.equal(Event.EventEmitter.getMaxListeners(), 222);
			assert.equal(Event.EventEmitter.getMaxListeners(Event.EventEmitter.GLOBAL_TARGET), 222);
		});

		it('subscribeOnce', () => {

			const eventName = 'test:event';
			const listener = () => {};
			const listener2 = () => {};
			const listener3 = () => {};
			const listener4 = () => {};

			Event.EventEmitter.subscribe(eventName, listener);
			Event.EventEmitter.subscribe(eventName, listener);
			Event.EventEmitter.subscribe(eventName, listener);
			Event.EventEmitter.subscribeOnce(eventName, listener);
			Event.EventEmitter.subscribeOnce(eventName, listener);
			Event.EventEmitter.subscribeOnce(eventName, listener);

			Event.EventEmitter.subscribeOnce(eventName, listener2);
			Event.EventEmitter.subscribeOnce(eventName, listener2);
			Event.EventEmitter.subscribeOnce(eventName, listener2);
			Event.EventEmitter.subscribe(eventName, listener2);
			Event.EventEmitter.subscribe(eventName, listener2);

			Event.EventEmitter.subscribe(Event.EventEmitter.GLOBAL_TARGET, eventName, listener3);
			Event.EventEmitter.subscribe(Event.EventEmitter.GLOBAL_TARGET, eventName, listener3);
			Event.EventEmitter.subscribe(Event.EventEmitter.GLOBAL_TARGET, eventName, listener3);
			Event.EventEmitter.subscribeOnce(Event.EventEmitter.GLOBAL_TARGET, eventName, listener3);
			Event.EventEmitter.subscribeOnce(Event.EventEmitter.GLOBAL_TARGET, eventName, listener3);
			Event.EventEmitter.subscribeOnce(Event.EventEmitter.GLOBAL_TARGET, eventName, listener3);

			Event.EventEmitter.subscribeOnce(Event.EventEmitter.GLOBAL_TARGET, eventName, listener4);
			Event.EventEmitter.subscribeOnce(Event.EventEmitter.GLOBAL_TARGET, eventName, listener4);
			Event.EventEmitter.subscribeOnce(Event.EventEmitter.GLOBAL_TARGET, eventName, listener4);
			Event.EventEmitter.subscribe(Event.EventEmitter.GLOBAL_TARGET, eventName, listener4);
			Event.EventEmitter.subscribe(Event.EventEmitter.GLOBAL_TARGET, eventName, listener4);

			assert.equal(Event.EventEmitter.getListeners(eventName).size, 4);
			assert.equal(Event.EventEmitter.getListeners(Event.EventEmitter.GLOBAL_TARGET, eventName).size, 4);

		});

		it('emitSync', (done) => {

			const eventName = 'event:async';
			const listener1 = () => {
				return new Promise((resolve) => {
					setTimeout(() => {
						resolve('value1');
					}, 500);
				});
			};
			const listener2 = () => {
				return new Promise((resolve) => {
					setTimeout(() => {
						resolve('value2');
					}, 700);
				});
			};
			const listener3 = () => {
				return new Promise((resolve) => {
					setTimeout(() => {
						resolve('value3');
					}, 900);
				});
			};

			Event.EventEmitter.subscribe(eventName, listener1);
			Event.EventEmitter.subscribe(Event.EventEmitter.GLOBAL_TARGET, eventName, listener2);
			Event.EventEmitter.subscribe(eventName, listener3);

			Event.EventEmitter
				.emitAsync(eventName)
				.then((results) => {
					assert.ok(results[0] === 'value1');
					assert.ok(results[1] === 'value2');
					assert.ok(results[2] === 'value3');

					done();
				});
		});
	});

	describe('Event Namespace', () => {

		it('Should subscribe on a short event name', () => {
			const emitter = new Event.EventEmitter();
			emitter.setEventNamespace('MyCompany.MyModule.MyClass');
			const eventName = 'onOpen';

			const listener1 = sinon.stub();
			const listener2 = sinon.stub();
			const listener3 = sinon.stub();

			emitter.subscribe(eventName, listener1);
			Event.EventEmitter.subscribe('MyCompany.MyModule.MyClass:onOpen', listener2);
			Event.EventEmitter.subscribe(emitter, 'onOpen', listener3);

			emitter.emit(eventName);

			assert(listener1.callCount === 1);
			assert(listener2.callCount === 1);
			assert(listener3.callCount === 1);
		});

		it('Should subscribe on a full event name if a namespace is empty', () => {
			const emitter = new Event.EventEmitter();
			const eventName = 'MyCompany.MyModule.MyClass:onOpen';

			const listener1 = sinon.stub();
			const listener2 = sinon.stub();
			const listener3 = sinon.stub();

			emitter.subscribe(eventName, listener1);
			Event.EventEmitter.subscribe('MyCompany.MyModule.MyClass:onOpen', listener2);
			Event.EventEmitter.subscribe(emitter, 'MyCompany.MyModule.MyClass:onOpen', listener3);

			emitter.emit(eventName);

			assert(listener1.callCount === 1);
			assert(listener2.callCount === 1);
			assert(listener3.callCount === 1);
		});

		it('Should subscribe on a plain object with a full event name', () => {
			const obj = {};
			const eventName = 'MyCompany.MyModule.MyObject:onOpen';

			const listener1 = sinon.stub().callsFake(function(a, b, c) {
				assert(a === 1);
				assert(b === 'string');
				assert(c === obj);
				assert(this === obj);
			});

			const listener2 = sinon.stub().callsFake(function(event) {

				const { a, b, c } = event.getData();

				assert.equal(a, 2);
				assert.equal(b, 'string2');
				assert(c === obj);
				assert(event.getTarget() === obj);
			});

			const listener3 = sinon.stub().callsFake(function(event) {

				const { a, b, c } = event.getData();

				assert.equal(a, 2);
				assert.equal(b, 'string2');
				assert(c === obj);
				assert(event.getTarget() === obj);
			});

			BX.addCustomEvent(obj, eventName, listener1);
			Event.EventEmitter.subscribe('MyCompany.MyModule.MyObject:onOpen', listener2);
			Event.EventEmitter.subscribe(obj, 'MyCompany.MyModule.MyObject:onOpen', listener3);

			Event.EventEmitter.emit(
				obj,
				'MyCompany.MyModule.MyObject:onOpen',
				new Event.BaseEvent({ compatData: [1, "string", obj], data: { a: 2, b: 'string2', c: obj } })
			);

			assert(listener1.callCount === 1);
			assert(listener2.callCount === 1);
			assert(listener3.callCount === 1);
		});
	});

	describe('Aliases', () => {

		Event.EventEmitter.registerAliases({
			onPopupClose: { namespace: 'MyCompany.MyModule.MyPopup', eventName: 'onClose' },
			onPopupOpen: { namespace: 'MyCompany.MyModule.MyPopup', eventName: 'onOpen' },
			onPopupHide: { namespace: 'MyCompany.MyModule.MyPopup', eventName: 'onHide' },
		});

		class MyPopup extends Event.EventEmitter
		{
			constructor()
			{
				super();
				this.setEventNamespace('MyCompany.MyModule.MyPopup');
			}

			show()
			{
				this.emit('onOpen');
			}

			close()
			{
				this.emit('onClose');
			}
		}

		class MySlider extends Event.EventEmitter
		{
			constructor()
			{
				super();
				this.setEventNamespace('MyCompany.MyModule.MySlider');
			}

			show()
			{
				this.emit('onOpen');
			}

			close()
			{
				this.emit('onClose');
			}
		}

		it('Should subscribe and unsubscribe old event names', () => {

			const onClose1 = sinon.stub();
			const onClose2 = sinon.stub();
			const onClose3Once = sinon.stub();
			const onClose4 = sinon.stub();
			const onClose5Once = sinon.stub();

			const onOpen1 = sinon.stub();
			const onOpen2 = sinon.stub();
			const onOpen3 = sinon.stub();

			const onHide1Once = sinon.stub();
			const onHide2 = sinon.stub();
			const onHide3 = sinon.stub();
			const onHide4 = sinon.stub();
			const onHide5 = sinon.stub();

			BX.addCustomEvent('onPopupClose', onClose1);
			Event.EventEmitter.subscribe('onPopupClose', onClose2);
			Event.EventEmitter.subscribeOnce('onPopupClose', onClose3Once);

			BX.addCustomEvent('onPopupOpen', onOpen1);
			Event.EventEmitter.subscribe('MyCompany.MyModule.MyPopup:onOpen', onOpen2);

			Event.EventEmitter.subscribeOnce('MyCompany.MyModule.MyPopup:onHide', onHide1Once);
			BX.addCustomEvent('onPopupHide', onHide2);

			assert.equal(Event.EventEmitter.getListeners('onPopupClose').size, 3);
			assert.equal(Event.EventEmitter.getListeners('onPopupOpen').size, 2);
			assert.equal(Event.EventEmitter.getListeners('onPopupHide').size, 2);
			assert.equal(Event.EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onClose').size, 3);
			assert.equal(Event.EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onOpen').size, 2);
			assert.equal(Event.EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onHide').size, 2);

			const popup = new MyPopup();
			popup.subscribe('onClose', onClose4);
			popup.subscribeOnce('onClose', onClose5Once);
			popup.subscribe('onOpen', onOpen3);
			popup.subscribe('onHide', onHide3);
			popup.subscribe('onHide', onHide4);
			popup.subscribe('onHide', onHide5);

			assert.equal(popup.getListeners('onClose').size, 2);
			assert.equal(popup.getListeners('onOpen').size, 1);
			assert.equal(popup.getListeners('onHide').size, 3);

			assert.equal(Event.EventEmitter.getListeners('onPopupClose').size, 3);
			assert.equal(Event.EventEmitter.getListeners('onPopupOpen').size, 2);
			assert.equal(Event.EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onClose').size, 3);
			assert.equal(Event.EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onOpen').size, 2);
			assert.equal(Event.EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onHide').size, 2);

			popup.show();
			popup.close();

			assert.equal(onClose1.callCount, 1);
			assert.equal(onClose2.callCount, 1);
			assert.equal(onClose3Once.callCount, 1);
			assert.equal(onClose4.callCount, 1);
			assert.equal(onClose5Once.callCount, 1);
			assert.equal(onOpen1.callCount, 1);
			assert.equal(onOpen2.callCount, 1);
			assert.equal(onOpen3.callCount, 1);
			assert.equal(onHide1Once.callCount, 0);
			assert.equal(onHide2.callCount, 0);
			assert.equal(onHide3.callCount, 0);

			popup.show();
			popup.close();

			assert.equal(onClose1.callCount, 2);
			assert.equal(onClose2.callCount, 2);
			assert.equal(onClose3Once.callCount, 1);
			assert.equal(onClose4.callCount, 2);
			assert.equal(onClose5Once.callCount, 1);
			assert.equal(onOpen1.callCount, 2);
			assert.equal(onOpen2.callCount, 2);
			assert.equal(onOpen3.callCount, 2);
			assert.equal(onHide1Once.callCount, 0);
			assert.equal(onHide2.callCount, 0);
			assert.equal(onHide3.callCount, 0);

			BX.onCustomEvent('onPopupClose');
			BX.onCustomEvent(popup, 'onClose');
			BX.onCustomEvent('MyCompany.MyModule.MyPopup:onClose');
			BX.onCustomEvent(popup, 'onClose');
			BX.onCustomEvent('MyCompany.MyModule.MyPopup:onOpen');
			BX.onCustomEvent(popup, 'onOpen');

			assert.equal(onClose1.callCount, 6);
			assert.equal(onClose2.callCount, 6);
			assert.equal(onClose3Once.callCount, 1);
			assert.equal(onClose4.callCount, 4);
			assert.equal(onClose5Once.callCount, 1);
			assert.equal(onOpen1.callCount, 4);
			assert.equal(onOpen2.callCount, 4);
			assert.equal(onOpen3.callCount, 3);
			assert.equal(onHide1Once.callCount, 0);
			assert.equal(onHide2.callCount, 0);
			assert.equal(onHide3.callCount, 0);

			Event.EventEmitter.emit('onPopupClose');
			Event.EventEmitter.emit(popup, 'onClose');
			Event.EventEmitter.emit('MyCompany.MyModule.MyPopup:onOpen');

			assert.equal(onClose1.callCount, 8);
			assert.equal(onClose2.callCount, 8);
			assert.equal(onClose3Once.callCount, 1);
			assert.equal(onClose4.callCount, 5);
			assert.equal(onClose5Once.callCount, 1);
			assert.equal(onOpen1.callCount, 5);
			assert.equal(onOpen2.callCount, 5);
			assert.equal(onOpen3.callCount, 3);
			assert.equal(onHide1Once.callCount, 0);
			assert.equal(onHide2.callCount, 0);
			assert.equal(onHide3.callCount, 0);

			assert.equal(popup.getListeners('onClose').size, 1);
			assert.equal(Event.EventEmitter.getListeners('onPopupClose').size, 2);
			assert.equal(Event.EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onHide').size, 2);

			BX.removeCustomEvent('onPopupClose', onClose1);
			BX.removeCustomEvent('onPopupOpen', onOpen1);
			BX.removeCustomEvent('onPopupHide', onHide1Once);

			assert.equal(popup.getListeners('onClose').size, 1);
			assert.equal(popup.getListeners('onOpen').size, 1);
			assert.equal(popup.getListeners('onHide').size, 3);
			assert.equal(Event.EventEmitter.getListeners('onPopupClose').size, 1);
			assert.equal(Event.EventEmitter.getListeners('onPopupOpen').size, 1);
			assert.equal(Event.EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onClose').size, 1);
			assert.equal(Event.EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onOpen').size, 1);
			assert.equal(Event.EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onHide').size, 1);

			Event.EventEmitter.unsubscribe('onPopupClose', onClose2);
			Event.EventEmitter.unsubscribe('MyCompany.MyModule.MyPopup:onClose', onClose3Once);
			Event.EventEmitter.unsubscribe('MyCompany.MyModule.MyPopup:onOpen', onOpen2);
			Event.EventEmitter.unsubscribe('MyCompany.MyModule.MyPopup:onHide', onHide2);

			assert.equal(popup.getListeners('onClose').size, 1);
			assert.equal(popup.getListeners('onOpen').size, 1);
			assert.equal(popup.getListeners('onHide').size, 3);

			assert.equal(Event.EventEmitter.getListeners('onPopupClose').size, 0);
			assert.equal(Event.EventEmitter.getListeners('onPopupOpen').size, 0);
			assert.equal(Event.EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onClose').size, 0);
			assert.equal(Event.EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onOpen').size, 0);
			assert.equal(Event.EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onHide').size, 0);

			popup.unsubscribe('onClose', onClose4);
			popup.unsubscribe('onClose', onClose5Once);
			popup.unsubscribe('onOpen', onOpen3);

			assert.equal(popup.getListeners('onClose').size, 0);
			assert.equal(popup.getListeners('onOpen').size, 0);
			assert.equal(popup.getListeners('onHide').size, 3);

			popup.unsubscribeAll('onHide');

			assert.equal(popup.getListeners('onHide').size, 0);
		});

		it('Should unsubscribe all event names', () => {
			const listener1 = sinon.stub();
			const listener2 = sinon.stub();
			const listener3 = sinon.stub();
			const listener4 = sinon.stub();
			const listener5 = sinon.stub();
			const listener6 = sinon.stub();
			const listener7 = sinon.stub();
			const listener8 = sinon.stub();
			const listenerOnce1 = sinon.stub();
			const listenerOnce2 = sinon.stub();
			const listenerOnce3 = sinon.stub();

			BX.addCustomEvent('onPopupClose', listener1);
			BX.addCustomEvent('onPopupOpen', listener2);
			BX.addCustomEvent('onPopupHide', listener7);

			Event.EventEmitter.subscribe('onPopupClose', listener3);
			Event.EventEmitter.subscribeOnce('onPopupClose', listenerOnce1);
			Event.EventEmitter.subscribe('MyCompany.MyModule.MyPopup:onOpen', listener4);
			Event.EventEmitter.subscribeOnce('MyCompany.MyModule.MyPopup:onHide', listenerOnce3);

			const popup = new MyPopup();
			popup.subscribe('onClose', listener5);
			popup.subscribeOnce('onClose', listenerOnce2);
			popup.subscribe('onOpen', listener6);
			popup.subscribe('onHide', listener8);

			assert.equal(Event.EventEmitter.getListeners('onPopupClose').size, 3);
			assert.equal(Event.EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onClose').size, 3);
			assert.equal(Event.EventEmitter.getListeners('onPopupOpen').size, 2);
			assert.equal(Event.EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onOpen').size, 2);
			assert.equal(Event.EventEmitter.getListeners('onPopupHide').size, 2);
			assert.equal(Event.EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onHide').size, 2);

			assert.equal(popup.getListeners('onClose').size, 2);
			assert.equal(popup.getListeners('onOpen').size, 1);
			assert.equal(popup.getListeners('onHide').size, 1);

			popup.unsubscribeAll('onClose');

			assert.equal(popup.getListeners('onClose').size, 0);
			assert.equal(popup.getListeners('onOpen').size, 1);
			assert.equal(popup.getListeners('onHide').size, 1);
			assert.equal(Event.EventEmitter.getListeners('onPopupClose').size, 3);
			assert.equal(Event.EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onClose').size, 3);
			assert.equal(Event.EventEmitter.getListeners('onPopupOpen').size, 2);
			assert.equal(Event.EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onOpen').size, 2);
			assert.equal(Event.EventEmitter.getListeners('onPopupHide').size, 2);
			assert.equal(Event.EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onHide').size, 2);

			Event.EventEmitter.unsubscribeAll('MyCompany.MyModule.MyPopup:onClose');

			assert.equal(popup.getListeners('onClose').size, 0);
			assert.equal(popup.getListeners('onOpen').size, 1);
			assert.equal(popup.getListeners('onHide').size, 1);
			assert.equal(Event.EventEmitter.getListeners('onPopupClose').size, 0);
			assert.equal(Event.EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onClose').size, 0);
			assert.equal(Event.EventEmitter.getListeners('onPopupOpen').size, 2);
			assert.equal(Event.EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onOpen').size, 2);
			assert.equal(Event.EventEmitter.getListeners('onPopupHide').size, 2);
			assert.equal(Event.EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onHide').size, 2);

			Event.EventEmitter.unsubscribeAll('onPopupHide');

			assert.equal(popup.getListeners('onClose').size, 0);
			assert.equal(popup.getListeners('onOpen').size, 1);
			assert.equal(popup.getListeners('onHide').size, 1);
			assert.equal(Event.EventEmitter.getListeners('onPopupClose').size, 0);
			assert.equal(Event.EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onClose').size, 0);
			assert.equal(Event.EventEmitter.getListeners('onPopupOpen').size, 2);
			assert.equal(Event.EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onOpen').size, 2);
			assert.equal(Event.EventEmitter.getListeners('onPopupHide').size, 0);
			assert.equal(Event.EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onHide').size, 0);

			popup.unsubscribeAll();
			Event.EventEmitter.unsubscribeAll('onPopupOpen');

			assert.equal(popup.getListeners('onClose').size, 0);
			assert.equal(popup.getListeners('onOpen').size, 0);
			assert.equal(popup.getListeners('onHide').size, 0);
			assert.equal(Event.EventEmitter.getListeners('onPopupClose').size, 0);
			assert.equal(Event.EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onClose').size, 0);
			assert.equal(Event.EventEmitter.getListeners('onPopupOpen').size, 0);
			assert.equal(Event.EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onOpen').size, 0);
			assert.equal(Event.EventEmitter.getListeners('onPopupHide').size, 0);
			assert.equal(Event.EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onHide').size, 0);
		});

		it('Should rebuild event map after an alias registration', () => {
			const listener1 = sinon.stub();
			const listener2 = sinon.stub();
			const listener3 = sinon.stub();
			const listener4 = sinon.stub();
			const listener5 = sinon.stub();
			const listener6 = sinon.stub();
			const listener7 = sinon.stub();
			const listener8 = sinon.stub();
			const listener9 = sinon.stub();
			const listener10 = sinon.stub();
			const listener11 = sinon.stub();
			const listener12 = sinon.stub();

			BX.addCustomEvent('onSliderClose', listener1);
			BX.addCustomEvent('onSliderOpen', listener2);
			BX.addCustomEvent('onSliderHide', listener3);

			Event.EventEmitter.setMaxListeners('onSliderOpen', 33);
			Event.EventEmitter.setMaxListeners('MyCompany.MyModule.MySlider:onOpen', 66);
			Event.EventEmitter.setMaxListeners('onSliderClose', 99);
			Event.EventEmitter.setMaxListeners('MyCompany.MyModule.MySlider:onHide', 10);

			Event.EventEmitter.subscribe('onSliderClose', listener4);
			Event.EventEmitter.subscribeOnce('onSliderClose', listener5);
			Event.EventEmitter.subscribe('MyCompany.MyModule.MySlider:onClose', listener11);
			Event.EventEmitter.subscribe('MyCompany.MyModule.MySlider:onClose', listener12);
			Event.EventEmitter.subscribe('MyCompany.MyModule.MySlider:onOpen', listener6);
			Event.EventEmitter.subscribe('MyCompany.MyModule.MySlider:onOpen', listener11);

			assert.equal(Event.EventEmitter.getListeners('onSliderClose').size, 3);
			assert.equal(Event.EventEmitter.getListeners('MyCompany.MyModule.MySlider:onClose').size, 2);
			assert.equal(Event.EventEmitter.getListeners('onSliderOpen').size, 1);
			assert.equal(Event.EventEmitter.getListeners('MyCompany.MyModule.MySlider:onOpen').size, 2);
			assert.equal(Event.EventEmitter.getListeners('onSliderHide').size, 1);
			assert.equal(Event.EventEmitter.getListeners('MyCompany.MyModule.MySlider:onHide').size, 0);


			const globalTargetMaxListeners = Event.EventEmitter.getMaxListeners();
			assert.equal(Event.EventEmitter.getMaxListeners('onSliderOpen'), 33);
			assert.equal(Event.EventEmitter.getMaxListeners('MyCompany.MyModule.MySlider:onOpen'), 66);
			assert.equal(Event.EventEmitter.getMaxListeners('onSliderClose'), 99);
			assert.equal(Event.EventEmitter.getMaxListeners('MyCompany.MyModule.MySlider:onClose'), globalTargetMaxListeners);
			assert.equal(Event.EventEmitter.getMaxListeners('MyCompany.MyModule.MySlider:onHide'), 10);
			assert.equal(Event.EventEmitter.getMaxListeners('onSliderHide'), globalTargetMaxListeners);

			Event.EventEmitter.registerAliases({
				onSliderClose: { namespace: 'MyCompany.MyModule.MySlider', eventName: 'onClose' },
				onSliderOpen: { namespace: 'MyCompany.MyModule.MySlider', eventName: 'onOpen' },
				onSliderHide: { namespace: 'MyCompany.MyModule.MySlider', eventName: 'onHide' },
			});

			Event.EventEmitter.subscribeOnce('MyCompany.MyModule.MySlider:onHide', listener7);

			assert.equal(Event.EventEmitter.getListeners('onSliderClose').size, 5);
			assert.equal(Event.EventEmitter.getListeners('MyCompany.MyModule.MySlider:onClose').size, 5);
			assert.equal(Event.EventEmitter.getListeners('onSliderOpen').size, 3);
			assert.equal(Event.EventEmitter.getListeners('MyCompany.MyModule.MySlider:onOpen').size, 3);
			assert.equal(Event.EventEmitter.getListeners('onSliderHide').size, 2);
			assert.equal(Event.EventEmitter.getListeners('MyCompany.MyModule.MySlider:onHide').size, 2);

			assert.equal(Event.EventEmitter.getMaxListeners('onSliderOpen'), 66);
			assert.equal(Event.EventEmitter.getMaxListeners('MyCompany.MyModule.MySlider:onOpen'), 66);
			assert.equal(Event.EventEmitter.getMaxListeners('onSliderClose'), 99);
			assert.equal(Event.EventEmitter.getMaxListeners('MyCompany.MyModule.MySlider:onHide'), 10);

			const slider = new MySlider();
			slider.subscribe('onClose', listener8);
			slider.subscribe('onClose', listener12);
			slider.subscribeOnce('onClose', listener9);
			slider.subscribe('onOpen', listener10);

			slider.show();
			slider.close();
			slider.show();
			slider.close();
			slider.emit('onHide');
			slider.emit('onHide');

			assert.equal(listener1.callCount, 2);
			assert.equal(listener2.callCount, 2);
			assert.equal(listener3.callCount, 2);
			assert.equal(listener4.callCount, 2);
			assert.equal(listener5.callCount, 1);
			assert.equal(listener6.callCount, 2);
			assert.equal(listener7.callCount, 1);
			assert.equal(listener8.callCount, 2);
			assert.equal(listener9.callCount, 1);
			assert.equal(listener10.callCount, 2);
			assert.equal(listener11.callCount, 4);
			assert.equal(listener12.callCount, 4);
		});

		class MyNewClass extends Event.EventEmitter
		{
			constructor(options)
			{
				super();
				this.setEventNamespace('MyModule.MyNewClass');
				this.subscribeFromOptions(options.events);
			}
		}

		const aliases = {
			onOldPopupClose: { namespace: 'MyModule.MyNewPopup', eventName: 'onClose' },
			onOldPopupOpen: { namespace: 'MyModule.MyNewPopup', eventName: 'onOpen' },
		};

		Event.EventEmitter.registerAliases(aliases);

		class MyNewPopup extends Event.EventEmitter
		{
			constructor(options)
			{
				super();
				this.setEventNamespace('MyModule.MyNewPopup');
				this.subscribeFromOptions(options.events, aliases);
			}
		}

		class MyOldSlider extends Event.EventEmitter
		{
			constructor(options)
			{
				super();
				this.setEventNamespace('MyModule.MyOldSlider');
				this.subscribeFromOptions(options.events, null, true);
			}
		}

		it('Should subscribe from options', () => {

			const listener1 = sinon.stub();
			const listener2 = sinon.stub();
			const listener3 = sinon.stub();
			const listener4 = sinon.stub();
			const listener5 = sinon.stub().callsFake((a, b, c) => {
				assert.equal(a, 1);
				assert.equal(b, 2);
				assert.equal(c, 3);
			});
			const listener6 = sinon.stub().callsFake((a, b, c) => {
				assert.equal(a, 1);
				assert.equal(b, 2);
				assert.equal(c, 3);
			});
			const listener7 = sinon.stub().callsFake((event) => {
				assert.equal(event.getData(), 100);

			});
			const listener8 = sinon.stub().callsFake((event) => {
				assert.equal(event.getData(), 100);
			});

			const newClass = new MyNewClass({
				events: {
					onOpen: listener1,
					onClose: listener2,
				}
			});

			assert.equal(newClass.getListeners('onOpen').size, 1);
			assert.equal(newClass.getListeners('onClose').size, 1);

			newClass.emit('onClose');
			newClass.emit('onClose');
			newClass.emit('onOpen');

			assert.equal(listener1.callCount, 1);
			assert.equal(listener2.callCount, 2);

			const oldSlider = new MyOldSlider({
				events: {
					onOpen: listener3,
					onClose: listener4,
				}
			});

			assert.equal(oldSlider.getListeners('onOpen').size, 1);
			assert.equal(oldSlider.getListeners('onClose').size, 1);

			oldSlider.emit('onClose');
			oldSlider.emit('onOpen');
			assert.equal(listener3.callCount, 1);
			assert.equal(listener4.callCount, 1);

			const newPopup = new MyNewPopup({
				events: {
					onOldPopupClose: listener5,
					onOldPopupOpen: listener6,
					onClose: listener7,
					onOpen: listener8,
				}
			});

			assert.equal(newPopup.getListeners('onOpen').size, 2);
			assert.equal(newPopup.getListeners('onClose').size, 2);

			const event = new Event.BaseEvent({ compatData: [1,2,3], data: 100 });
			newPopup.emit('onClose', event);
			newPopup.emit('onClose', event);
			newPopup.emit('onClose', event);
			newPopup.emit('onOpen', event);
			newPopup.emit('onOpen', event);

			assert.equal(listener5.callCount, 3);
			assert.equal(listener6.callCount, 2);
			assert.equal(listener7.callCount, 3);
			assert.equal(listener8.callCount, 2);

		});
	});

	describe('Event errors', () => {
		it('Should add errors', () => {

			const listener1 = (event) => {
				event.setError(new BaseError('There is an error 1.', 'my-error-1'));
			};

			const listener2 = (event) => {
				event.setError(new BaseError('There is an error 2.', 'my-error-2', { code: 123 }));
			};

			const listener3 = (event) => {
				event.preventDefault();
			};

			const emitter = new Event.EventEmitter();

			emitter.subscribe('onClose', listener1);
			emitter.subscribe('onClose', listener2);
			emitter.subscribe('onOpen', listener2);
			emitter.subscribe('onHide', listener3);

			const event1 = new Event.BaseEvent();
			const event2 = new Event.BaseEvent();
			const event3 = new Event.BaseEvent();

			emitter.emit('onClose', event1);
			emitter.emit('onOpen', event2);
			emitter.emit('onHide', event3);

			assert.equal(event1.getErrors().length, 2);
			assert.equal(event2.getErrors().length, 1);
			assert.equal(event3.getErrors().length, 0);

			assert.equal(event1.getErrors()[0].getMessage(), 'There is an error 1.');
			assert.equal(event1.getErrors()[0].getCode(), 'my-error-1');
			assert.equal(event1.getErrors()[0].getCustomData(), null);

			assert.equal(event1.getErrors()[1].getMessage(), 'There is an error 2.');
			assert.equal(event1.getErrors()[1].getCode(), 'my-error-2');
			assert.equal(event1.getErrors()[1].getCustomData().code, 123);

			assert.equal(event2.getErrors()[0].getMessage(), 'There is an error 2.');
			assert.equal(event2.getErrors()[0].getCode(), 'my-error-2');
			assert.equal(event2.getErrors()[0].getCustomData().code, 123);
		});
	});
});