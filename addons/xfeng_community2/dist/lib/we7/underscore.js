'use strict';

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

//     Underscore.js 1.8.2
//     http://underscorejs.org
//     (c) 2009-2015 Jeremy Ashkenas, DocumentCloud and Investigative Reporters & Editors
//     Underscore may be freely distributed under the MIT license.

(function () {

	// Baseline setup
	// --------------

	// Establish the root object, `window` in the browser, or `exports` on the server.
	//   var root = this;

	//   // Save the previous value of the `_` variable.
	//   var previousUnderscore = root._;

	// Save bytes in the minified (but not gzipped) version:
	var ArrayProto = Array.prototype,
	    ObjProto = Object.prototype,
	    FuncProto = Function.prototype;

	// Create quick reference variables for speed access to core prototypes.
	var push = ArrayProto.push,
	    slice = ArrayProto.slice,
	    toString = ObjProto.toString,
	    hasOwnProperty = ObjProto.hasOwnProperty;

	// All **ECMAScript 5** native function implementations that we hope to use
	// are declared here.
	var nativeIsArray = Array.isArray,
	    nativeKeys = Object.keys,
	    nativeBind = FuncProto.bind,
	    nativeCreate = Object.create;

	// Naked function reference for surrogate-prototype-swapping.
	var Ctor = function Ctor() {};

	// Create a safe reference to the Underscore object for use below.
	var _ = function _(obj) {
		if (obj instanceof _) return obj;
		if (!(this instanceof _)) return new _(obj);
		this._wrapped = obj;
	};

	// Export the Underscore object for **Node.js**, with
	// backwards-compatibility for the old `require()` API. If we're in
	// the browser, add `_` as a global object.
	//   if (typeof exports !== 'undefined') {
	//     if (typeof module !== 'undefined' && module.exports) {
	//       exports = module.exports = _;
	//     }
	//     exports._ = _;
	//   } else {
	//     root._ = _;
	//   }
	module.exports = _;
	// Current version.
	_.VERSION = '1.8.2';

	// Internal function that returns an efficient (for current engines) version
	// of the passed-in callback, to be repeatedly applied in other Underscore
	// functions.
	var optimizeCb = function optimizeCb(func, context, argCount) {
		if (context === void 0) return func;
		switch (argCount == null ? 3 : argCount) {
			case 1:
				return function (value) {
					return func.call(context, value);
				};
			case 2:
				return function (value, other) {
					return func.call(context, value, other);
				};
			case 3:
				return function (value, index, collection) {
					return func.call(context, value, index, collection);
				};
			case 4:
				return function (accumulator, value, index, collection) {
					return func.call(context, accumulator, value, index, collection);
				};
		}
		return function () {
			return func.apply(context, arguments);
		};
	};

	// A mostly-internal function to generate callbacks that can be applied
	// to each element in a collection, returning the desired result 鈥� either
	// identity, an arbitrary callback, a property matcher, or a property accessor.
	var cb = function cb(value, context, argCount) {
		if (value == null) return _.identity;
		if (_.isFunction(value)) return optimizeCb(value, context, argCount);
		if (_.isObject(value)) return _.matcher(value);
		return _.property(value);
	};
	_.iteratee = function (value, context) {
		return cb(value, context, Infinity);
	};

	// An internal function for creating assigner functions.
	var createAssigner = function createAssigner(keysFunc, undefinedOnly) {
		return function (obj) {
			var length = arguments.length;
			if (length < 2 || obj == null) return obj;
			for (var index = 1; index < length; index++) {
				var source = arguments[index],
				    keys = keysFunc(source),
				    l = keys.length;
				for (var i = 0; i < l; i++) {
					var key = keys[i];
					if (!undefinedOnly || obj[key] === void 0) obj[key] = source[key];
				}
			}
			return obj;
		};
	};

	// An internal function for creating a new object that inherits from another.
	var baseCreate = function baseCreate(prototype) {
		if (!_.isObject(prototype)) return {};
		if (nativeCreate) return nativeCreate(prototype);
		Ctor.prototype = prototype;
		var result = new Ctor();
		Ctor.prototype = null;
		return result;
	};

	// Helper for collection methods to determine whether a collection
	// should be iterated as an array or as an object
	// Related: http://people.mozilla.org/~jorendorff/es6-draft.html#sec-tolength
	var MAX_ARRAY_INDEX = Math.pow(2, 53) - 1;
	var isArrayLike = function isArrayLike(collection) {
		var length = collection != null && collection.length;
		return typeof length == 'number' && length >= 0 && length <= MAX_ARRAY_INDEX;
	};

	// Collection Functions
	// --------------------

	// The cornerstone, an `each` implementation, aka `forEach`.
	// Handles raw objects in addition to array-likes. Treats all
	// sparse array-likes as if they were dense.
	_.each = _.forEach = function (obj, iteratee, context) {
		iteratee = optimizeCb(iteratee, context);
		var i, length;
		if (isArrayLike(obj)) {
			for (i = 0, length = obj.length; i < length; i++) {
				iteratee(obj[i], i, obj);
			}
		} else {
			var keys = _.keys(obj);
			for (i = 0, length = keys.length; i < length; i++) {
				iteratee(obj[keys[i]], keys[i], obj);
			}
		}
		return obj;
	};

	// Return the results of applying the iteratee to each element.
	_.map = _.collect = function (obj, iteratee, context) {
		iteratee = cb(iteratee, context);
		var keys = !isArrayLike(obj) && _.keys(obj),
		    length = (keys || obj).length,
		    results = Array(length);
		for (var index = 0; index < length; index++) {
			var currentKey = keys ? keys[index] : index;
			results[index] = iteratee(obj[currentKey], currentKey, obj);
		}
		return results;
	};

	// Create a reducing function iterating left or right.
	function createReduce(dir) {
		// Optimized iterator function as using arguments.length
		// in the main function will deoptimize the, see #1991.
		function iterator(obj, iteratee, memo, keys, index, length) {
			for (; index >= 0 && index < length; index += dir) {
				var currentKey = keys ? keys[index] : index;
				memo = iteratee(memo, obj[currentKey], currentKey, obj);
			}
			return memo;
		}

		return function (obj, iteratee, memo, context) {
			iteratee = optimizeCb(iteratee, context, 4);
			var keys = !isArrayLike(obj) && _.keys(obj),
			    length = (keys || obj).length,
			    index = dir > 0 ? 0 : length - 1;
			// Determine the initial value if none is provided.
			if (arguments.length < 3) {
				memo = obj[keys ? keys[index] : index];
				index += dir;
			}
			return iterator(obj, iteratee, memo, keys, index, length);
		};
	}

	// **Reduce** builds up a single result from a list of values, aka `inject`,
	// or `foldl`.
	_.reduce = _.foldl = _.inject = createReduce(1);

	// The right-associative version of reduce, also known as `foldr`.
	_.reduceRight = _.foldr = createReduce(-1);

	// Return the first value which passes a truth test. Aliased as `detect`.
	_.find = _.detect = function (obj, predicate, context) {
		var key;
		if (isArrayLike(obj)) {
			key = _.findIndex(obj, predicate, context);
		} else {
			key = _.findKey(obj, predicate, context);
		}
		if (key !== void 0 && key !== -1) return obj[key];
	};

	// Return all the elements that pass a truth test.
	// Aliased as `select`.
	_.filter = _.select = function (obj, predicate, context) {
		var results = [];
		predicate = cb(predicate, context);
		_.each(obj, function (value, index, list) {
			if (predicate(value, index, list)) results.push(value);
		});
		return results;
	};

	// Return all the elements for which a truth test fails.
	_.reject = function (obj, predicate, context) {
		return _.filter(obj, _.negate(cb(predicate)), context);
	};

	// Determine whether all of the elements match a truth test.
	// Aliased as `all`.
	_.every = _.all = function (obj, predicate, context) {
		predicate = cb(predicate, context);
		var keys = !isArrayLike(obj) && _.keys(obj),
		    length = (keys || obj).length;
		for (var index = 0; index < length; index++) {
			var currentKey = keys ? keys[index] : index;
			if (!predicate(obj[currentKey], currentKey, obj)) return false;
		}
		return true;
	};

	// Determine if at least one element in the object matches a truth test.
	// Aliased as `any`.
	_.some = _.any = function (obj, predicate, context) {
		predicate = cb(predicate, context);
		var keys = !isArrayLike(obj) && _.keys(obj),
		    length = (keys || obj).length;
		for (var index = 0; index < length; index++) {
			var currentKey = keys ? keys[index] : index;
			if (predicate(obj[currentKey], currentKey, obj)) return true;
		}
		return false;
	};

	// Determine if the array or object contains a given value (using `===`).
	// Aliased as `includes` and `include`.
	_.contains = _.includes = _.include = function (obj, target, fromIndex) {
		if (!isArrayLike(obj)) obj = _.values(obj);
		return _.indexOf(obj, target, typeof fromIndex == 'number' && fromIndex) >= 0;
	};

	// Invoke a method (with arguments) on every item in a collection.
	_.invoke = function (obj, method) {
		var args = slice.call(arguments, 2);
		var isFunc = _.isFunction(method);
		return _.map(obj, function (value) {
			var func = isFunc ? method : value[method];
			return func == null ? func : func.apply(value, args);
		});
	};

	// Convenience version of a common use case of `map`: fetching a property.
	_.pluck = function (obj, key) {
		return _.map(obj, _.property(key));
	};

	// Convenience version of a common use case of `filter`: selecting only objects
	// containing specific `key:value` pairs.
	_.where = function (obj, attrs) {
		return _.filter(obj, _.matcher(attrs));
	};

	// Convenience version of a common use case of `find`: getting the first object
	// containing specific `key:value` pairs.
	_.findWhere = function (obj, attrs) {
		return _.find(obj, _.matcher(attrs));
	};

	// Return the maximum element (or element-based computation).
	_.max = function (obj, iteratee, context) {
		var result = -Infinity,
		    lastComputed = -Infinity,
		    value,
		    computed;
		if (iteratee == null && obj != null) {
			obj = isArrayLike(obj) ? obj : _.values(obj);
			for (var i = 0, length = obj.length; i < length; i++) {
				value = obj[i];
				if (value > result) {
					result = value;
				}
			}
		} else {
			iteratee = cb(iteratee, context);
			_.each(obj, function (value, index, list) {
				computed = iteratee(value, index, list);
				if (computed > lastComputed || computed === -Infinity && result === -Infinity) {
					result = value;
					lastComputed = computed;
				}
			});
		}
		return result;
	};

	// Return the minimum element (or element-based computation).
	_.min = function (obj, iteratee, context) {
		var result = Infinity,
		    lastComputed = Infinity,
		    value,
		    computed;
		if (iteratee == null && obj != null) {
			obj = isArrayLike(obj) ? obj : _.values(obj);
			for (var i = 0, length = obj.length; i < length; i++) {
				value = obj[i];
				if (value < result) {
					result = value;
				}
			}
		} else {
			iteratee = cb(iteratee, context);
			_.each(obj, function (value, index, list) {
				computed = iteratee(value, index, list);
				if (computed < lastComputed || computed === Infinity && result === Infinity) {
					result = value;
					lastComputed = computed;
				}
			});
		}
		return result;
	};

	// Shuffle a collection, using the modern version of the
	// [Fisher-Yates shuffle](http://en.wikipedia.org/wiki/Fisher鈥揧ates_shuffle).
	_.shuffle = function (obj) {
		var set = isArrayLike(obj) ? obj : _.values(obj);
		var length = set.length;
		var shuffled = Array(length);
		for (var index = 0, rand; index < length; index++) {
			rand = _.random(0, index);
			if (rand !== index) shuffled[index] = shuffled[rand];
			shuffled[rand] = set[index];
		}
		return shuffled;
	};

	// Sample **n** random values from a collection.
	// If **n** is not specified, returns a single random element.
	// The internal `guard` argument allows it to work with `map`.
	_.sample = function (obj, n, guard) {
		if (n == null || guard) {
			if (!isArrayLike(obj)) obj = _.values(obj);
			return obj[_.random(obj.length - 1)];
		}
		return _.shuffle(obj).slice(0, Math.max(0, n));
	};

	// Sort the object's values by a criterion produced by an iteratee.
	_.sortBy = function (obj, iteratee, context) {
		iteratee = cb(iteratee, context);
		return _.pluck(_.map(obj, function (value, index, list) {
			return {
				value: value,
				index: index,
				criteria: iteratee(value, index, list)
			};
		}).sort(function (left, right) {
			var a = left.criteria;
			var b = right.criteria;
			if (a !== b) {
				if (a > b || a === void 0) return 1;
				if (a < b || b === void 0) return -1;
			}
			return left.index - right.index;
		}), 'value');
	};

	// An internal function used for aggregate "group by" operations.
	var group = function group(behavior) {
		return function (obj, iteratee, context) {
			var result = {};
			iteratee = cb(iteratee, context);
			_.each(obj, function (value, index) {
				var key = iteratee(value, index, obj);
				behavior(result, value, key);
			});
			return result;
		};
	};

	// Groups the object's values by a criterion. Pass either a string attribute
	// to group by, or a function that returns the criterion.
	_.groupBy = group(function (result, value, key) {
		if (_.has(result, key)) result[key].push(value);else result[key] = [value];
	});

	// Indexes the object's values by a criterion, similar to `groupBy`, but for
	// when you know that your index values will be unique.
	_.indexBy = group(function (result, value, key) {
		result[key] = value;
	});

	// Counts instances of an object that group by a certain criterion. Pass
	// either a string attribute to count by, or a function that returns the
	// criterion.
	_.countBy = group(function (result, value, key) {
		if (_.has(result, key)) result[key]++;else result[key] = 1;
	});

	// Safely create a real, live array from anything iterable.
	_.toArray = function (obj) {
		if (!obj) return [];
		if (_.isArray(obj)) return slice.call(obj);
		if (isArrayLike(obj)) return _.map(obj, _.identity);
		return _.values(obj);
	};

	// Return the number of elements in an object.
	_.size = function (obj) {
		if (obj == null) return 0;
		return isArrayLike(obj) ? obj.length : _.keys(obj).length;
	};

	// Split a collection into two arrays: one whose elements all satisfy the given
	// predicate, and one whose elements all do not satisfy the predicate.
	_.partition = function (obj, predicate, context) {
		predicate = cb(predicate, context);
		var pass = [],
		    fail = [];
		_.each(obj, function (value, key, obj) {
			(predicate(value, key, obj) ? pass : fail).push(value);
		});
		return [pass, fail];
	};

	// Array Functions
	// ---------------

	// Get the first element of an array. Passing **n** will return the first N
	// values in the array. Aliased as `head` and `take`. The **guard** check
	// allows it to work with `_.map`.
	_.first = _.head = _.take = function (array, n, guard) {
		if (array == null) return void 0;
		if (n == null || guard) return array[0];
		return _.initial(array, array.length - n);
	};

	// Returns everything but the last entry of the array. Especially useful on
	// the arguments object. Passing **n** will return all the values in
	// the array, excluding the last N.
	_.initial = function (array, n, guard) {
		return slice.call(array, 0, Math.max(0, array.length - (n == null || guard ? 1 : n)));
	};

	// Get the last element of an array. Passing **n** will return the last N
	// values in the array.
	_.last = function (array, n, guard) {
		if (array == null) return void 0;
		if (n == null || guard) return array[array.length - 1];
		return _.rest(array, Math.max(0, array.length - n));
	};

	// Returns everything but the first entry of the array. Aliased as `tail` and `drop`.
	// Especially useful on the arguments object. Passing an **n** will return
	// the rest N values in the array.
	_.rest = _.tail = _.drop = function (array, n, guard) {
		return slice.call(array, n == null || guard ? 1 : n);
	};

	// Trim out all falsy values from an array.
	_.compact = function (array) {
		return _.filter(array, _.identity);
	};

	// Internal implementation of a recursive `flatten` function.
	var flatten = function flatten(input, shallow, strict, startIndex) {
		var output = [],
		    idx = 0;
		for (var i = startIndex || 0, length = input && input.length; i < length; i++) {
			var value = input[i];
			if (isArrayLike(value) && (_.isArray(value) || _.isArguments(value))) {
				//flatten current level of array or arguments object
				if (!shallow) value = flatten(value, shallow, strict);
				var j = 0,
				    len = value.length;
				output.length += len;
				while (j < len) {
					output[idx++] = value[j++];
				}
			} else if (!strict) {
				output[idx++] = value;
			}
		}
		return output;
	};

	// Flatten out an array, either recursively (by default), or just one level.
	_.flatten = function (array, shallow) {
		return flatten(array, shallow, false);
	};

	// Return a version of the array that does not contain the specified value(s).
	_.without = function (array) {
		return _.difference(array, slice.call(arguments, 1));
	};

	// Produce a duplicate-free version of the array. If the array has already
	// been sorted, you have the option of using a faster algorithm.
	// Aliased as `unique`.
	_.uniq = _.unique = function (array, isSorted, iteratee, context) {
		if (array == null) return [];
		if (!_.isBoolean(isSorted)) {
			context = iteratee;
			iteratee = isSorted;
			isSorted = false;
		}
		if (iteratee != null) iteratee = cb(iteratee, context);
		var result = [];
		var seen = [];
		for (var i = 0, length = array.length; i < length; i++) {
			var value = array[i],
			    computed = iteratee ? iteratee(value, i, array) : value;
			if (isSorted) {
				if (!i || seen !== computed) result.push(value);
				seen = computed;
			} else if (iteratee) {
				if (!_.contains(seen, computed)) {
					seen.push(computed);
					result.push(value);
				}
			} else if (!_.contains(result, value)) {
				result.push(value);
			}
		}
		return result;
	};

	// Produce an array that contains the union: each distinct element from all of
	// the passed-in arrays.
	_.union = function () {
		return _.uniq(flatten(arguments, true, true));
	};

	// Produce an array that contains every item shared between all the
	// passed-in arrays.
	_.intersection = function (array) {
		if (array == null) return [];
		var result = [];
		var argsLength = arguments.length;
		for (var i = 0, length = array.length; i < length; i++) {
			var item = array[i];
			if (_.contains(result, item)) continue;
			for (var j = 1; j < argsLength; j++) {
				if (!_.contains(arguments[j], item)) break;
			}
			if (j === argsLength) result.push(item);
		}
		return result;
	};

	// Take the difference between one array and a number of other arrays.
	// Only the elements present in just the first array will remain.
	_.difference = function (array) {
		var rest = flatten(arguments, true, true, 1);
		return _.filter(array, function (value) {
			return !_.contains(rest, value);
		});
	};

	// Zip together multiple lists into a single array -- elements that share
	// an index go together.
	_.zip = function () {
		return _.unzip(arguments);
	};

	// Complement of _.zip. Unzip accepts an array of arrays and groups
	// each array's elements on shared indices
	_.unzip = function (array) {
		var length = array && _.max(array, 'length').length || 0;
		var result = Array(length);

		for (var index = 0; index < length; index++) {
			result[index] = _.pluck(array, index);
		}
		return result;
	};

	// Converts lists into objects. Pass either a single array of `[key, value]`
	// pairs, or two parallel arrays of the same length -- one of keys, and one of
	// the corresponding values.
	_.object = function (list, values) {
		var result = {};
		for (var i = 0, length = list && list.length; i < length; i++) {
			if (values) {
				result[list[i]] = values[i];
			} else {
				result[list[i][0]] = list[i][1];
			}
		}
		return result;
	};

	// Return the position of the first occurrence of an item in an array,
	// or -1 if the item is not included in the array.
	// If the array is large and already in sort order, pass `true`
	// for **isSorted** to use binary search.
	_.indexOf = function (array, item, isSorted) {
		var i = 0,
		    length = array && array.length;
		if (typeof isSorted == 'number') {
			i = isSorted < 0 ? Math.max(0, length + isSorted) : isSorted;
		} else if (isSorted && length) {
			i = _.sortedIndex(array, item);
			return array[i] === item ? i : -1;
		}
		if (item !== item) {
			return _.findIndex(slice.call(array, i), _.isNaN);
		}
		for (; i < length; i++) {
			if (array[i] === item) return i;
		}return -1;
	};

	_.lastIndexOf = function (array, item, from) {
		var idx = array ? array.length : 0;
		if (typeof from == 'number') {
			idx = from < 0 ? idx + from + 1 : Math.min(idx, from + 1);
		}
		if (item !== item) {
			return _.findLastIndex(slice.call(array, 0, idx), _.isNaN);
		}
		while (--idx >= 0) {
			if (array[idx] === item) return idx;
		}return -1;
	};

	// Generator function to create the findIndex and findLastIndex functions
	function createIndexFinder(dir) {
		return function (array, predicate, context) {
			predicate = cb(predicate, context);
			var length = array != null && array.length;
			var index = dir > 0 ? 0 : length - 1;
			for (; index >= 0 && index < length; index += dir) {
				if (predicate(array[index], index, array)) return index;
			}
			return -1;
		};
	}

	// Returns the first index on an array-like that passes a predicate test
	_.findIndex = createIndexFinder(1);

	_.findLastIndex = createIndexFinder(-1);

	// Use a comparator function to figure out the smallest index at which
	// an object should be inserted so as to maintain order. Uses binary search.
	_.sortedIndex = function (array, obj, iteratee, context) {
		iteratee = cb(iteratee, context, 1);
		var value = iteratee(obj);
		var low = 0,
		    high = array.length;
		while (low < high) {
			var mid = Math.floor((low + high) / 2);
			if (iteratee(array[mid]) < value) low = mid + 1;else high = mid;
		}
		return low;
	};

	// Generate an integer Array containing an arithmetic progression. A port of
	// the native Python `range()` function. See
	// [the Python documentation](http://docs.python.org/library/functions.html#range).
	_.range = function (start, stop, step) {
		if (arguments.length <= 1) {
			stop = start || 0;
			start = 0;
		}
		step = step || 1;

		var length = Math.max(Math.ceil((stop - start) / step), 0);
		var range = Array(length);

		for (var idx = 0; idx < length; idx++, start += step) {
			range[idx] = start;
		}

		return range;
	};

	// Function (ahem) Functions
	// ------------------

	// Determines whether to execute a function as a constructor
	// or a normal function with the provided arguments
	var executeBound = function executeBound(sourceFunc, boundFunc, context, callingContext, args) {
		if (!(callingContext instanceof boundFunc)) return sourceFunc.apply(context, args);
		var self = baseCreate(sourceFunc.prototype);
		var result = sourceFunc.apply(self, args);
		if (_.isObject(result)) return result;
		return self;
	};

	// Create a function bound to a given object (assigning `this`, and arguments,
	// optionally). Delegates to **ECMAScript 5**'s native `Function.bind` if
	// available.
	_.bind = function (func, context) {
		if (nativeBind && func.bind === nativeBind) return nativeBind.apply(func, slice.call(arguments, 1));
		if (!_.isFunction(func)) throw new TypeError('Bind must be called on a function');
		var args = slice.call(arguments, 2);
		var bound = function bound() {
			return executeBound(func, bound, context, this, args.concat(slice.call(arguments)));
		};
		return bound;
	};

	// Partially apply a function by creating a version that has had some of its
	// arguments pre-filled, without changing its dynamic `this` context. _ acts
	// as a placeholder, allowing any combination of arguments to be pre-filled.
	_.partial = function (func) {
		var boundArgs = slice.call(arguments, 1);
		var bound = function bound() {
			var position = 0,
			    length = boundArgs.length;
			var args = Array(length);
			for (var i = 0; i < length; i++) {
				args[i] = boundArgs[i] === _ ? arguments[position++] : boundArgs[i];
			}
			while (position < arguments.length) {
				args.push(arguments[position++]);
			}return executeBound(func, bound, this, this, args);
		};
		return bound;
	};

	// Bind a number of an object's methods to that object. Remaining arguments
	// are the method names to be bound. Useful for ensuring that all callbacks
	// defined on an object belong to it.
	_.bindAll = function (obj) {
		var i,
		    length = arguments.length,
		    key;
		if (length <= 1) throw new Error('bindAll must be passed function names');
		for (i = 1; i < length; i++) {
			key = arguments[i];
			obj[key] = _.bind(obj[key], obj);
		}
		return obj;
	};

	// Memoize an expensive function by storing its results.
	_.memoize = function (func, hasher) {
		var memoize = function memoize(key) {
			var cache = memoize.cache;
			var address = '' + (hasher ? hasher.apply(this, arguments) : key);
			if (!_.has(cache, address)) cache[address] = func.apply(this, arguments);
			return cache[address];
		};
		memoize.cache = {};
		return memoize;
	};

	// Delays a function for the given number of milliseconds, and then calls
	// it with the arguments supplied.
	_.delay = function (func, wait) {
		var args = slice.call(arguments, 2);
		return setTimeout(function () {
			return func.apply(null, args);
		}, wait);
	};

	// Defers a function, scheduling it to run after the current call stack has
	// cleared.
	_.defer = _.partial(_.delay, _, 1);

	// Returns a function, that, when invoked, will only be triggered at most once
	// during a given window of time. Normally, the throttled function will run
	// as much as it can, without ever going more than once per `wait` duration;
	// but if you'd like to disable the execution on the leading edge, pass
	// `{leading: false}`. To disable execution on the trailing edge, ditto.
	_.throttle = function (func, wait, options) {
		var context, args, result;
		var timeout = null;
		var previous = 0;
		if (!options) options = {};
		var later = function later() {
			previous = options.leading === false ? 0 : _.now();
			timeout = null;
			result = func.apply(context, args);
			if (!timeout) context = args = null;
		};
		return function () {
			var now = _.now();
			if (!previous && options.leading === false) previous = now;
			var remaining = wait - (now - previous);
			context = this;
			args = arguments;
			if (remaining <= 0 || remaining > wait) {
				if (timeout) {
					clearTimeout(timeout);
					timeout = null;
				}
				previous = now;
				result = func.apply(context, args);
				if (!timeout) context = args = null;
			} else if (!timeout && options.trailing !== false) {
				timeout = setTimeout(later, remaining);
			}
			return result;
		};
	};

	// Returns a function, that, as long as it continues to be invoked, will not
	// be triggered. The function will be called after it stops being called for
	// N milliseconds. If `immediate` is passed, trigger the function on the
	// leading edge, instead of the trailing.
	_.debounce = function (func, wait, immediate) {
		var timeout, args, context, timestamp, result;

		var later = function later() {
			var last = _.now() - timestamp;

			if (last < wait && last >= 0) {
				timeout = setTimeout(later, wait - last);
			} else {
				timeout = null;
				if (!immediate) {
					result = func.apply(context, args);
					if (!timeout) context = args = null;
				}
			}
		};

		return function () {
			context = this;
			args = arguments;
			timestamp = _.now();
			var callNow = immediate && !timeout;
			if (!timeout) timeout = setTimeout(later, wait);
			if (callNow) {
				result = func.apply(context, args);
				context = args = null;
			}

			return result;
		};
	};

	// Returns the first function passed as an argument to the second,
	// allowing you to adjust arguments, run code before and after, and
	// conditionally execute the original function.
	_.wrap = function (func, wrapper) {
		return _.partial(wrapper, func);
	};

	// Returns a negated version of the passed-in predicate.
	_.negate = function (predicate) {
		return function () {
			return !predicate.apply(this, arguments);
		};
	};

	// Returns a function that is the composition of a list of functions, each
	// consuming the return value of the function that follows.
	_.compose = function () {
		var args = arguments;
		var start = args.length - 1;
		return function () {
			var i = start;
			var result = args[start].apply(this, arguments);
			while (i--) {
				result = args[i].call(this, result);
			}return result;
		};
	};

	// Returns a function that will only be executed on and after the Nth call.
	_.after = function (times, func) {
		return function () {
			if (--times < 1) {
				return func.apply(this, arguments);
			}
		};
	};

	// Returns a function that will only be executed up to (but not including) the Nth call.
	_.before = function (times, func) {
		var memo;
		return function () {
			if (--times > 0) {
				memo = func.apply(this, arguments);
			}
			if (times <= 1) func = null;
			return memo;
		};
	};

	// Returns a function that will be executed at most one time, no matter how
	// often you call it. Useful for lazy initialization.
	_.once = _.partial(_.before, 2);

	// Object Functions
	// ----------------

	// Keys in IE < 9 that won't be iterated by `for key in ...` and thus missed.
	var hasEnumBug = !{ toString: null }.propertyIsEnumerable('toString');
	var nonEnumerableProps = ['valueOf', 'isPrototypeOf', 'toString', 'propertyIsEnumerable', 'hasOwnProperty', 'toLocaleString'];

	function collectNonEnumProps(obj, keys) {
		var nonEnumIdx = nonEnumerableProps.length;
		var constructor = obj.constructor;
		var proto = _.isFunction(constructor) && constructor.prototype || ObjProto;

		// Constructor is a special case.
		var prop = 'constructor';
		if (_.has(obj, prop) && !_.contains(keys, prop)) keys.push(prop);

		while (nonEnumIdx--) {
			prop = nonEnumerableProps[nonEnumIdx];
			if (prop in obj && obj[prop] !== proto[prop] && !_.contains(keys, prop)) {
				keys.push(prop);
			}
		}
	}

	// Retrieve the names of an object's own properties.
	// Delegates to **ECMAScript 5**'s native `Object.keys`
	_.keys = function (obj) {
		if (!_.isObject(obj)) return [];
		if (nativeKeys) return nativeKeys(obj);
		var keys = [];
		for (var key in obj) {
			if (_.has(obj, key)) keys.push(key);
		} // Ahem, IE < 9.
		if (hasEnumBug) collectNonEnumProps(obj, keys);
		return keys;
	};

	// Retrieve all the property names of an object.
	_.allKeys = function (obj) {
		if (!_.isObject(obj)) return [];
		var keys = [];
		for (var key in obj) {
			keys.push(key);
		} // Ahem, IE < 9.
		if (hasEnumBug) collectNonEnumProps(obj, keys);
		return keys;
	};

	// Retrieve the values of an object's properties.
	_.values = function (obj) {
		var keys = _.keys(obj);
		var length = keys.length;
		var values = Array(length);
		for (var i = 0; i < length; i++) {
			values[i] = obj[keys[i]];
		}
		return values;
	};

	// Returns the results of applying the iteratee to each element of the object
	// In contrast to _.map it returns an object
	_.mapObject = function (obj, iteratee, context) {
		iteratee = cb(iteratee, context);
		var keys = _.keys(obj),
		    length = keys.length,
		    results = {},
		    currentKey;
		for (var index = 0; index < length; index++) {
			currentKey = keys[index];
			results[currentKey] = iteratee(obj[currentKey], currentKey, obj);
		}
		return results;
	};

	// Convert an object into a list of `[key, value]` pairs.
	_.pairs = function (obj) {
		var keys = _.keys(obj);
		var length = keys.length;
		var pairs = Array(length);
		for (var i = 0; i < length; i++) {
			pairs[i] = [keys[i], obj[keys[i]]];
		}
		return pairs;
	};

	// Invert the keys and values of an object. The values must be serializable.
	_.invert = function (obj) {
		var result = {};
		var keys = _.keys(obj);
		for (var i = 0, length = keys.length; i < length; i++) {
			result[obj[keys[i]]] = keys[i];
		}
		return result;
	};

	// Return a sorted list of the function names available on the object.
	// Aliased as `methods`
	_.functions = _.methods = function (obj) {
		var names = [];
		for (var key in obj) {
			if (_.isFunction(obj[key])) names.push(key);
		}
		return names.sort();
	};

	// Extend a given object with all the properties in passed-in object(s).
	_.extend = createAssigner(_.allKeys);

	// Assigns a given object with all the own properties in the passed-in object(s)
	// (https://developer.mozilla.org/docs/Web/JavaScript/Reference/Global_Objects/Object/assign)
	_.extendOwn = _.assign = createAssigner(_.keys);

	// Returns the first key on an object that passes a predicate test
	_.findKey = function (obj, predicate, context) {
		predicate = cb(predicate, context);
		var keys = _.keys(obj),
		    key;
		for (var i = 0, length = keys.length; i < length; i++) {
			key = keys[i];
			if (predicate(obj[key], key, obj)) return key;
		}
	};

	// Return a copy of the object only containing the whitelisted properties.
	_.pick = function (object, oiteratee, context) {
		var result = {},
		    obj = object,
		    iteratee,
		    keys;
		if (obj == null) return result;
		if (_.isFunction(oiteratee)) {
			keys = _.allKeys(obj);
			iteratee = optimizeCb(oiteratee, context);
		} else {
			keys = flatten(arguments, false, false, 1);
			iteratee = function iteratee(value, key, obj) {
				return key in obj;
			};
			obj = Object(obj);
		}
		for (var i = 0, length = keys.length; i < length; i++) {
			var key = keys[i];
			var value = obj[key];
			if (iteratee(value, key, obj)) result[key] = value;
		}
		return result;
	};

	// Return a copy of the object without the blacklisted properties.
	_.omit = function (obj, iteratee, context) {
		if (_.isFunction(iteratee)) {
			iteratee = _.negate(iteratee);
		} else {
			var keys = _.map(flatten(arguments, false, false, 1), String);
			iteratee = function iteratee(value, key) {
				return !_.contains(keys, key);
			};
		}
		return _.pick(obj, iteratee, context);
	};

	// Fill in a given object with default properties.
	_.defaults = createAssigner(_.allKeys, true);

	// Creates an object that inherits from the given prototype object.
	// If additional properties are provided then they will be added to the
	// created object.
	_.create = function (prototype, props) {
		var result = baseCreate(prototype);
		if (props) _.extendOwn(result, props);
		return result;
	};

	// Create a (shallow-cloned) duplicate of an object.
	_.clone = function (obj) {
		if (!_.isObject(obj)) return obj;
		return _.isArray(obj) ? obj.slice() : _.extend({}, obj);
	};

	// Invokes interceptor with the obj, and then returns obj.
	// The primary purpose of this method is to "tap into" a method chain, in
	// order to perform operations on intermediate results within the chain.
	_.tap = function (obj, interceptor) {
		interceptor(obj);
		return obj;
	};

	// Returns whether an object has a given set of `key:value` pairs.
	_.isMatch = function (object, attrs) {
		var keys = _.keys(attrs),
		    length = keys.length;
		if (object == null) return !length;
		var obj = Object(object);
		for (var i = 0; i < length; i++) {
			var key = keys[i];
			if (attrs[key] !== obj[key] || !(key in obj)) return false;
		}
		return true;
	};

	// Internal recursive comparison function for `isEqual`.
	var eq = function eq(a, b, aStack, bStack) {
		// Identical objects are equal. `0 === -0`, but they aren't identical.
		// See the [Harmony `egal` proposal](http://wiki.ecmascript.org/doku.php?id=harmony:egal).
		if (a === b) return a !== 0 || 1 / a === 1 / b;
		// A strict comparison is necessary because `null == undefined`.
		if (a == null || b == null) return a === b;
		// Unwrap any wrapped objects.
		if (a instanceof _) a = a._wrapped;
		if (b instanceof _) b = b._wrapped;
		// Compare `[[Class]]` names.
		var className = toString.call(a);
		if (className !== toString.call(b)) return false;
		switch (className) {
			// Strings, numbers, regular expressions, dates, and booleans are compared by value.
			case '[object RegExp]':
			// RegExps are coerced to strings for comparison (Note: '' + /a/i === '/a/i')
			case '[object String]':
				// Primitives and their corresponding object wrappers are equivalent; thus, `"5"` is
				// equivalent to `new String("5")`.
				return '' + a === '' + b;
			case '[object Number]':
				// `NaN`s are equivalent, but non-reflexive.
				// Object(NaN) is equivalent to NaN
				if (+a !== +a) return +b !== +b;
				// An `egal` comparison is performed for other numeric values.
				return +a === 0 ? 1 / +a === 1 / b : +a === +b;
			case '[object Date]':
			case '[object Boolean]':
				// Coerce dates and booleans to numeric primitive values. Dates are compared by their
				// millisecond representations. Note that invalid dates with millisecond representations
				// of `NaN` are not equivalent.
				return +a === +b;
		}

		var areArrays = className === '[object Array]';
		if (!areArrays) {
			if ((typeof a === 'undefined' ? 'undefined' : _typeof(a)) != 'object' || (typeof b === 'undefined' ? 'undefined' : _typeof(b)) != 'object') return false;

			// Objects with different constructors are not equivalent, but `Object`s or `Array`s
			// from different frames are.
			var aCtor = a.constructor,
			    bCtor = b.constructor;
			if (aCtor !== bCtor && !(_.isFunction(aCtor) && aCtor instanceof aCtor && _.isFunction(bCtor) && bCtor instanceof bCtor) && 'constructor' in a && 'constructor' in b) {
				return false;
			}
		}
		// Assume equality for cyclic structures. The algorithm for detecting cyclic
		// structures is adapted from ES 5.1 section 15.12.3, abstract operation `JO`.

		// Initializing stack of traversed objects.
		// It's done here since we only need them for objects and arrays comparison.
		aStack = aStack || [];
		bStack = bStack || [];
		var length = aStack.length;
		while (length--) {
			// Linear search. Performance is inversely proportional to the number of
			// unique nested structures.
			if (aStack[length] === a) return bStack[length] === b;
		}

		// Add the first object to the stack of traversed objects.
		aStack.push(a);
		bStack.push(b);

		// Recursively compare objects and arrays.
		if (areArrays) {
			// Compare array lengths to determine if a deep comparison is necessary.
			length = a.length;
			if (length !== b.length) return false;
			// Deep compare the contents, ignoring non-numeric properties.
			while (length--) {
				if (!eq(a[length], b[length], aStack, bStack)) return false;
			}
		} else {
			// Deep compare objects.
			var keys = _.keys(a),
			    key;
			length = keys.length;
			// Ensure that both objects contain the same number of properties before comparing deep equality.
			if (_.keys(b).length !== length) return false;
			while (length--) {
				// Deep compare each member
				key = keys[length];
				if (!(_.has(b, key) && eq(a[key], b[key], aStack, bStack))) return false;
			}
		}
		// Remove the first object from the stack of traversed objects.
		aStack.pop();
		bStack.pop();
		return true;
	};

	// Perform a deep comparison to check if two objects are equal.
	_.isEqual = function (a, b) {
		return eq(a, b);
	};

	// Is a given array, string, or object empty?
	// An "empty" object has no enumerable own-properties.
	_.isEmpty = function (obj) {
		if (obj == null) return true;
		if (isArrayLike(obj) && (_.isArray(obj) || _.isString(obj) || _.isArguments(obj))) return obj.length === 0;
		return _.keys(obj).length === 0;
	};

	// Is a given value a DOM element?
	_.isElement = function (obj) {
		return !!(obj && obj.nodeType === 1);
	};

	// Is a given value an array?
	// Delegates to ECMA5's native Array.isArray
	_.isArray = nativeIsArray || function (obj) {
		return toString.call(obj) === '[object Array]';
	};

	// Is a given variable an object?
	_.isObject = function (obj) {
		var type = typeof obj === 'undefined' ? 'undefined' : _typeof(obj);
		return type === 'function' || type === 'object' && !!obj;
	};

	// Add some isType methods: isArguments, isFunction, isString, isNumber, isDate, isRegExp, isError.
	_.each(['Arguments', 'Function', 'String', 'Number', 'Date', 'RegExp', 'Error'], function (name) {
		_['is' + name] = function (obj) {
			return toString.call(obj) === '[object ' + name + ']';
		};
	});

	// Define a fallback version of the method in browsers (ahem, IE < 9), where
	// there isn't any inspectable "Arguments" type.
	if (!_.isArguments(arguments)) {
		_.isArguments = function (obj) {
			return _.has(obj, 'callee');
		};
	}

	// Optimize `isFunction` if appropriate. Work around some typeof bugs in old v8,
	// IE 11 (#1621), and in Safari 8 (#1929).
	if (typeof /./ != 'function' && (typeof Int8Array === 'undefined' ? 'undefined' : _typeof(Int8Array)) != 'object') {
		_.isFunction = function (obj) {
			return typeof obj == 'function' || false;
		};
	}

	// Is a given object a finite number?
	_.isFinite = function (obj) {
		return isFinite(obj) && !isNaN(parseFloat(obj));
	};

	// Is the given value `NaN`? (NaN is the only number which does not equal itself).
	_.isNaN = function (obj) {
		return _.isNumber(obj) && obj !== +obj;
	};

	// Is a given value a boolean?
	_.isBoolean = function (obj) {
		return obj === true || obj === false || toString.call(obj) === '[object Boolean]';
	};

	// Is a given value equal to null?
	_.isNull = function (obj) {
		return obj === null;
	};

	// Is a given variable undefined?
	_.isUndefined = function (obj) {
		return obj === void 0;
	};

	// Shortcut function for checking if an object has a given property directly
	// on itself (in other words, not on a prototype).
	_.has = function (obj, key) {
		return obj != null && hasOwnProperty.call(obj, key);
	};

	// Utility Functions
	// -----------------

	// Run Underscore.js in *noConflict* mode, returning the `_` variable to its
	// previous owner. Returns a reference to the Underscore object.
	_.noConflict = function () {
		root._ = previousUnderscore;
		return this;
	};

	// Keep the identity function around for default iteratees.
	_.identity = function (value) {
		return value;
	};

	// Predicate-generating functions. Often useful outside of Underscore.
	_.constant = function (value) {
		return function () {
			return value;
		};
	};

	_.noop = function () {};

	_.property = function (key) {
		return function (obj) {
			return obj == null ? void 0 : obj[key];
		};
	};

	// Generates a function for a given object that returns a given property.
	_.propertyOf = function (obj) {
		return obj == null ? function () {} : function (key) {
			return obj[key];
		};
	};

	// Returns a predicate for checking whether an object has a given set of 
	// `key:value` pairs.
	_.matcher = _.matches = function (attrs) {
		attrs = _.extendOwn({}, attrs);
		return function (obj) {
			return _.isMatch(obj, attrs);
		};
	};

	// Run a function **n** times.
	_.times = function (n, iteratee, context) {
		var accum = Array(Math.max(0, n));
		iteratee = optimizeCb(iteratee, context, 1);
		for (var i = 0; i < n; i++) {
			accum[i] = iteratee(i);
		}return accum;
	};

	// Return a random integer between min and max (inclusive).
	_.random = function (min, max) {
		if (max == null) {
			max = min;
			min = 0;
		}
		return min + Math.floor(Math.random() * (max - min + 1));
	};

	// A (possibly faster) way to get the current timestamp as an integer.
	_.now = Date.now || function () {
		return new Date().getTime();
	};

	// List of HTML entities for escaping.
	var escapeMap = {
		'&': '&amp;',
		'<': '&lt;',
		'>': '&gt;',
		'"': '&quot;',
		"'": '&#x27;',
		'`': '&#x60;'
	};
	var unescapeMap = _.invert(escapeMap);

	// Functions for escaping and unescaping strings to/from HTML interpolation.
	var createEscaper = function createEscaper(map) {
		var escaper = function escaper(match) {
			return map[match];
		};
		// Regexes for identifying a key that needs to be escaped
		var source = '(?:' + _.keys(map).join('|') + ')';
		var testRegexp = RegExp(source);
		var replaceRegexp = RegExp(source, 'g');
		return function (string) {
			string = string == null ? '' : '' + string;
			return testRegexp.test(string) ? string.replace(replaceRegexp, escaper) : string;
		};
	};
	_.escape = createEscaper(escapeMap);
	_.unescape = createEscaper(unescapeMap);

	// If the value of the named `property` is a function then invoke it with the
	// `object` as context; otherwise, return it.
	_.result = function (object, property, fallback) {
		var value = object == null ? void 0 : object[property];
		if (value === void 0) {
			value = fallback;
		}
		return _.isFunction(value) ? value.call(object) : value;
	};

	// Generate a unique integer id (unique within the entire client session).
	// Useful for temporary DOM ids.
	var idCounter = 0;
	_.uniqueId = function (prefix) {
		var id = ++idCounter + '';
		return prefix ? prefix + id : id;
	};

	// By default, Underscore uses ERB-style template delimiters, change the
	// following template settings to use alternative delimiters.
	_.templateSettings = {
		evaluate: /<%([\s\S]+?)%>/g,
		interpolate: /<%=([\s\S]+?)%>/g,
		escape: /<%-([\s\S]+?)%>/g
	};

	// When customizing `templateSettings`, if you don't want to define an
	// interpolation, evaluation or escaping regex, we need one that is
	// guaranteed not to match.
	var noMatch = /(.)^/;

	// Certain characters need to be escaped so that they can be put into a
	// string literal.
	var escapes = {
		"'": "'",
		'\\': '\\',
		'\r': 'r',
		'\n': 'n',
		'\u2028': 'u2028',
		'\u2029': 'u2029'
	};

	var escaper = /\\|'|\r|\n|\u2028|\u2029/g;

	var escapeChar = function escapeChar(match) {
		return '\\' + escapes[match];
	};

	// JavaScript micro-templating, similar to John Resig's implementation.
	// Underscore templating handles arbitrary delimiters, preserves whitespace,
	// and correctly escapes quotes within interpolated code.
	// NB: `oldSettings` only exists for backwards compatibility.
	_.template = function (text, settings, oldSettings) {
		if (!settings && oldSettings) settings = oldSettings;
		settings = _.defaults({}, settings, _.templateSettings);

		// Combine delimiters into one regular expression via alternation.
		var matcher = RegExp([(settings.escape || noMatch).source, (settings.interpolate || noMatch).source, (settings.evaluate || noMatch).source].join('|') + '|$', 'g');

		// Compile the template source, escaping string literals appropriately.
		var index = 0;
		var source = "__p+='";
		text.replace(matcher, function (match, escape, interpolate, evaluate, offset) {
			source += text.slice(index, offset).replace(escaper, escapeChar);
			index = offset + match.length;

			if (escape) {
				source += "'+\n((__t=(" + escape + "))==null?'':_.escape(__t))+\n'";
			} else if (interpolate) {
				source += "'+\n((__t=(" + interpolate + "))==null?'':__t)+\n'";
			} else if (evaluate) {
				source += "';\n" + evaluate + "\n__p+='";
			}

			// Adobe VMs need the match returned to produce the correct offest.
			return match;
		});
		source += "';\n";

		// If a variable is not specified, place data values in local scope.
		if (!settings.variable) source = 'with(obj||{}){\n' + source + '}\n';

		source = "var __t,__p='',__j=Array.prototype.join," + "print=function(){__p+=__j.call(arguments,'');};\n" + source + 'return __p;\n';

		try {
			var render = new Function(settings.variable || 'obj', '_', source);
		} catch (e) {
			e.source = source;
			throw e;
		}

		var template = function template(data) {
			return render.call(this, data, _);
		};

		// Provide the compiled source as a convenience for precompilation.
		var argument = settings.variable || 'obj';
		template.source = 'function(' + argument + '){\n' + source + '}';

		return template;
	};

	// Add a "chain" function. Start chaining a wrapped Underscore object.
	_.chain = function (obj) {
		var instance = _(obj);
		instance._chain = true;
		return instance;
	};

	// OOP
	// ---------------
	// If Underscore is called as a function, it returns a wrapped object that
	// can be used OO-style. This wrapper holds altered versions of all the
	// underscore functions. Wrapped objects may be chained.

	// Helper function to continue chaining intermediate results.
	var result = function result(instance, obj) {
		return instance._chain ? _(obj).chain() : obj;
	};

	// Add your own custom functions to the Underscore object.
	_.mixin = function (obj) {
		_.each(_.functions(obj), function (name) {
			var func = _[name] = obj[name];
			_.prototype[name] = function () {
				var args = [this._wrapped];
				push.apply(args, arguments);
				return result(this, func.apply(_, args));
			};
		});
	};

	// Add all of the Underscore functions to the wrapper object.
	_.mixin(_);

	// Add all mutator Array functions to the wrapper.
	_.each(['pop', 'push', 'reverse', 'shift', 'sort', 'splice', 'unshift'], function (name) {
		var method = ArrayProto[name];
		_.prototype[name] = function () {
			var obj = this._wrapped;
			method.apply(obj, arguments);
			if ((name === 'shift' || name === 'splice') && obj.length === 0) delete obj[0];
			return result(this, obj);
		};
	});

	// Add all accessor Array functions to the wrapper.
	_.each(['concat', 'join', 'slice'], function (name) {
		var method = ArrayProto[name];
		_.prototype[name] = function () {
			return result(this, method.apply(this._wrapped, arguments));
		};
	});

	// Extracts the result from a wrapped and chained object.
	_.prototype.value = function () {
		return this._wrapped;
	};

	// Provide unwrapping proxy for some methods used in engine operations
	// such as arithmetic and JSON stringification.
	_.prototype.valueOf = _.prototype.toJSON = _.prototype.value;

	_.prototype.toString = function () {
		return '' + this._wrapped;
	};

	// AMD registration happens at the end for compatibility with AMD loaders
	// that may not enforce next-turn semantics on modules. Even though general
	// practice for AMD registration is to be anonymous, underscore registers
	// as a named module because, like jQuery, it is a base library that is
	// popular enough to be bundled in a third party lib, but not be part of
	// an AMD load request. Those cases could generate an error when an
	// anonymous define() is called outside of a loader request.
	//   if (typeof define === 'function' && define.amd) {
	//     define('underscore', [], function() {
	//       return _;
	//     });
	//   }
}).call(undefined);
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbInVuZGVyc2NvcmUuanMiXSwibmFtZXMiOlsiQXJyYXlQcm90byIsIkFycmF5IiwicHJvdG90eXBlIiwiT2JqUHJvdG8iLCJPYmplY3QiLCJGdW5jUHJvdG8iLCJGdW5jdGlvbiIsInB1c2giLCJzbGljZSIsInRvU3RyaW5nIiwiaGFzT3duUHJvcGVydHkiLCJuYXRpdmVJc0FycmF5IiwiaXNBcnJheSIsIm5hdGl2ZUtleXMiLCJrZXlzIiwibmF0aXZlQmluZCIsImJpbmQiLCJuYXRpdmVDcmVhdGUiLCJjcmVhdGUiLCJDdG9yIiwiXyIsIm9iaiIsIl93cmFwcGVkIiwibW9kdWxlIiwiZXhwb3J0cyIsIlZFUlNJT04iLCJvcHRpbWl6ZUNiIiwiZnVuYyIsImNvbnRleHQiLCJhcmdDb3VudCIsInZhbHVlIiwiY2FsbCIsIm90aGVyIiwiaW5kZXgiLCJjb2xsZWN0aW9uIiwiYWNjdW11bGF0b3IiLCJhcHBseSIsImFyZ3VtZW50cyIsImNiIiwiaWRlbnRpdHkiLCJpc0Z1bmN0aW9uIiwiaXNPYmplY3QiLCJtYXRjaGVyIiwicHJvcGVydHkiLCJpdGVyYXRlZSIsIkluZmluaXR5IiwiY3JlYXRlQXNzaWduZXIiLCJrZXlzRnVuYyIsInVuZGVmaW5lZE9ubHkiLCJsZW5ndGgiLCJzb3VyY2UiLCJsIiwiaSIsImtleSIsImJhc2VDcmVhdGUiLCJyZXN1bHQiLCJNQVhfQVJSQVlfSU5ERVgiLCJNYXRoIiwicG93IiwiaXNBcnJheUxpa2UiLCJlYWNoIiwiZm9yRWFjaCIsIm1hcCIsImNvbGxlY3QiLCJyZXN1bHRzIiwiY3VycmVudEtleSIsImNyZWF0ZVJlZHVjZSIsImRpciIsIml0ZXJhdG9yIiwibWVtbyIsInJlZHVjZSIsImZvbGRsIiwiaW5qZWN0IiwicmVkdWNlUmlnaHQiLCJmb2xkciIsImZpbmQiLCJkZXRlY3QiLCJwcmVkaWNhdGUiLCJmaW5kSW5kZXgiLCJmaW5kS2V5IiwiZmlsdGVyIiwic2VsZWN0IiwibGlzdCIsInJlamVjdCIsIm5lZ2F0ZSIsImV2ZXJ5IiwiYWxsIiwic29tZSIsImFueSIsImNvbnRhaW5zIiwiaW5jbHVkZXMiLCJpbmNsdWRlIiwidGFyZ2V0IiwiZnJvbUluZGV4IiwidmFsdWVzIiwiaW5kZXhPZiIsImludm9rZSIsIm1ldGhvZCIsImFyZ3MiLCJpc0Z1bmMiLCJwbHVjayIsIndoZXJlIiwiYXR0cnMiLCJmaW5kV2hlcmUiLCJtYXgiLCJsYXN0Q29tcHV0ZWQiLCJjb21wdXRlZCIsIm1pbiIsInNodWZmbGUiLCJzZXQiLCJzaHVmZmxlZCIsInJhbmQiLCJyYW5kb20iLCJzYW1wbGUiLCJuIiwiZ3VhcmQiLCJzb3J0QnkiLCJjcml0ZXJpYSIsInNvcnQiLCJsZWZ0IiwicmlnaHQiLCJhIiwiYiIsImdyb3VwIiwiYmVoYXZpb3IiLCJncm91cEJ5IiwiaGFzIiwiaW5kZXhCeSIsImNvdW50QnkiLCJ0b0FycmF5Iiwic2l6ZSIsInBhcnRpdGlvbiIsInBhc3MiLCJmYWlsIiwiZmlyc3QiLCJoZWFkIiwidGFrZSIsImFycmF5IiwiaW5pdGlhbCIsImxhc3QiLCJyZXN0IiwidGFpbCIsImRyb3AiLCJjb21wYWN0IiwiZmxhdHRlbiIsImlucHV0Iiwic2hhbGxvdyIsInN0cmljdCIsInN0YXJ0SW5kZXgiLCJvdXRwdXQiLCJpZHgiLCJpc0FyZ3VtZW50cyIsImoiLCJsZW4iLCJ3aXRob3V0IiwiZGlmZmVyZW5jZSIsInVuaXEiLCJ1bmlxdWUiLCJpc1NvcnRlZCIsImlzQm9vbGVhbiIsInNlZW4iLCJ1bmlvbiIsImludGVyc2VjdGlvbiIsImFyZ3NMZW5ndGgiLCJpdGVtIiwiemlwIiwidW56aXAiLCJvYmplY3QiLCJzb3J0ZWRJbmRleCIsImlzTmFOIiwibGFzdEluZGV4T2YiLCJmcm9tIiwiZmluZExhc3RJbmRleCIsImNyZWF0ZUluZGV4RmluZGVyIiwibG93IiwiaGlnaCIsIm1pZCIsImZsb29yIiwicmFuZ2UiLCJzdGFydCIsInN0b3AiLCJzdGVwIiwiY2VpbCIsImV4ZWN1dGVCb3VuZCIsInNvdXJjZUZ1bmMiLCJib3VuZEZ1bmMiLCJjYWxsaW5nQ29udGV4dCIsInNlbGYiLCJUeXBlRXJyb3IiLCJib3VuZCIsImNvbmNhdCIsInBhcnRpYWwiLCJib3VuZEFyZ3MiLCJwb3NpdGlvbiIsImJpbmRBbGwiLCJFcnJvciIsIm1lbW9pemUiLCJoYXNoZXIiLCJjYWNoZSIsImFkZHJlc3MiLCJkZWxheSIsIndhaXQiLCJzZXRUaW1lb3V0IiwiZGVmZXIiLCJ0aHJvdHRsZSIsIm9wdGlvbnMiLCJ0aW1lb3V0IiwicHJldmlvdXMiLCJsYXRlciIsImxlYWRpbmciLCJub3ciLCJyZW1haW5pbmciLCJjbGVhclRpbWVvdXQiLCJ0cmFpbGluZyIsImRlYm91bmNlIiwiaW1tZWRpYXRlIiwidGltZXN0YW1wIiwiY2FsbE5vdyIsIndyYXAiLCJ3cmFwcGVyIiwiY29tcG9zZSIsImFmdGVyIiwidGltZXMiLCJiZWZvcmUiLCJvbmNlIiwiaGFzRW51bUJ1ZyIsInByb3BlcnR5SXNFbnVtZXJhYmxlIiwibm9uRW51bWVyYWJsZVByb3BzIiwiY29sbGVjdE5vbkVudW1Qcm9wcyIsIm5vbkVudW1JZHgiLCJjb25zdHJ1Y3RvciIsInByb3RvIiwicHJvcCIsImFsbEtleXMiLCJtYXBPYmplY3QiLCJwYWlycyIsImludmVydCIsImZ1bmN0aW9ucyIsIm1ldGhvZHMiLCJuYW1lcyIsImV4dGVuZCIsImV4dGVuZE93biIsImFzc2lnbiIsInBpY2siLCJvaXRlcmF0ZWUiLCJvbWl0IiwiU3RyaW5nIiwiZGVmYXVsdHMiLCJwcm9wcyIsImNsb25lIiwidGFwIiwiaW50ZXJjZXB0b3IiLCJpc01hdGNoIiwiZXEiLCJhU3RhY2siLCJiU3RhY2siLCJjbGFzc05hbWUiLCJhcmVBcnJheXMiLCJhQ3RvciIsImJDdG9yIiwicG9wIiwiaXNFcXVhbCIsImlzRW1wdHkiLCJpc1N0cmluZyIsImlzRWxlbWVudCIsIm5vZGVUeXBlIiwidHlwZSIsIm5hbWUiLCJJbnQ4QXJyYXkiLCJpc0Zpbml0ZSIsInBhcnNlRmxvYXQiLCJpc051bWJlciIsImlzTnVsbCIsImlzVW5kZWZpbmVkIiwibm9Db25mbGljdCIsInJvb3QiLCJwcmV2aW91c1VuZGVyc2NvcmUiLCJjb25zdGFudCIsIm5vb3AiLCJwcm9wZXJ0eU9mIiwibWF0Y2hlcyIsImFjY3VtIiwiRGF0ZSIsImdldFRpbWUiLCJlc2NhcGVNYXAiLCJ1bmVzY2FwZU1hcCIsImNyZWF0ZUVzY2FwZXIiLCJlc2NhcGVyIiwibWF0Y2giLCJqb2luIiwidGVzdFJlZ2V4cCIsIlJlZ0V4cCIsInJlcGxhY2VSZWdleHAiLCJzdHJpbmciLCJ0ZXN0IiwicmVwbGFjZSIsImVzY2FwZSIsInVuZXNjYXBlIiwiZmFsbGJhY2siLCJpZENvdW50ZXIiLCJ1bmlxdWVJZCIsInByZWZpeCIsImlkIiwidGVtcGxhdGVTZXR0aW5ncyIsImV2YWx1YXRlIiwiaW50ZXJwb2xhdGUiLCJub01hdGNoIiwiZXNjYXBlcyIsImVzY2FwZUNoYXIiLCJ0ZW1wbGF0ZSIsInRleHQiLCJzZXR0aW5ncyIsIm9sZFNldHRpbmdzIiwib2Zmc2V0IiwidmFyaWFibGUiLCJyZW5kZXIiLCJlIiwiZGF0YSIsImFyZ3VtZW50IiwiY2hhaW4iLCJpbnN0YW5jZSIsIl9jaGFpbiIsIm1peGluIiwidmFsdWVPZiIsInRvSlNPTiJdLCJtYXBwaW5ncyI6Ijs7OztBQUFBO0FBQ0E7QUFDQTtBQUNBOztBQUVDLGFBQVk7O0FBRVo7QUFDQTs7QUFFQTtBQUNBOztBQUVBO0FBQ0E7O0FBRUE7QUFDQSxLQUFJQSxhQUFhQyxNQUFNQyxTQUF2QjtBQUFBLEtBQWtDQyxXQUFXQyxPQUFPRixTQUFwRDtBQUFBLEtBQStERyxZQUFZQyxTQUFTSixTQUFwRjs7QUFFQTtBQUNBLEtBQ0NLLE9BQU9QLFdBQVdPLElBRG5CO0FBQUEsS0FFQ0MsUUFBUVIsV0FBV1EsS0FGcEI7QUFBQSxLQUdDQyxXQUFXTixTQUFTTSxRQUhyQjtBQUFBLEtBSUNDLGlCQUFpQlAsU0FBU08sY0FKM0I7O0FBTUE7QUFDQTtBQUNBLEtBQ0NDLGdCQUFnQlYsTUFBTVcsT0FEdkI7QUFBQSxLQUVDQyxhQUFhVCxPQUFPVSxJQUZyQjtBQUFBLEtBR0NDLGFBQWFWLFVBQVVXLElBSHhCO0FBQUEsS0FJQ0MsZUFBZWIsT0FBT2MsTUFKdkI7O0FBTUE7QUFDQSxLQUFJQyxPQUFPLFNBQVBBLElBQU8sR0FBWSxDQUFHLENBQTFCOztBQUVBO0FBQ0EsS0FBSUMsSUFBSSxTQUFKQSxDQUFJLENBQVVDLEdBQVYsRUFBZTtBQUN0QixNQUFJQSxlQUFlRCxDQUFuQixFQUFzQixPQUFPQyxHQUFQO0FBQ3RCLE1BQUksRUFBRSxnQkFBZ0JELENBQWxCLENBQUosRUFBMEIsT0FBTyxJQUFJQSxDQUFKLENBQU1DLEdBQU4sQ0FBUDtBQUMxQixPQUFLQyxRQUFMLEdBQWdCRCxHQUFoQjtBQUNBLEVBSkQ7O0FBTUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBRSxRQUFPQyxPQUFQLEdBQWlCSixDQUFqQjtBQUNBO0FBQ0FBLEdBQUVLLE9BQUYsR0FBWSxPQUFaOztBQUVBO0FBQ0E7QUFDQTtBQUNBLEtBQUlDLGFBQWEsU0FBYkEsVUFBYSxDQUFVQyxJQUFWLEVBQWdCQyxPQUFoQixFQUF5QkMsUUFBekIsRUFBbUM7QUFDbkQsTUFBSUQsWUFBWSxLQUFLLENBQXJCLEVBQXdCLE9BQU9ELElBQVA7QUFDeEIsVUFBUUUsWUFBWSxJQUFaLEdBQW1CLENBQW5CLEdBQXVCQSxRQUEvQjtBQUNDLFFBQUssQ0FBTDtBQUFRLFdBQU8sVUFBVUMsS0FBVixFQUFpQjtBQUMvQixZQUFPSCxLQUFLSSxJQUFMLENBQVVILE9BQVYsRUFBbUJFLEtBQW5CLENBQVA7QUFDQSxLQUZPO0FBR1IsUUFBSyxDQUFMO0FBQVEsV0FBTyxVQUFVQSxLQUFWLEVBQWlCRSxLQUFqQixFQUF3QjtBQUN0QyxZQUFPTCxLQUFLSSxJQUFMLENBQVVILE9BQVYsRUFBbUJFLEtBQW5CLEVBQTBCRSxLQUExQixDQUFQO0FBQ0EsS0FGTztBQUdSLFFBQUssQ0FBTDtBQUFRLFdBQU8sVUFBVUYsS0FBVixFQUFpQkcsS0FBakIsRUFBd0JDLFVBQXhCLEVBQW9DO0FBQ2xELFlBQU9QLEtBQUtJLElBQUwsQ0FBVUgsT0FBVixFQUFtQkUsS0FBbkIsRUFBMEJHLEtBQTFCLEVBQWlDQyxVQUFqQyxDQUFQO0FBQ0EsS0FGTztBQUdSLFFBQUssQ0FBTDtBQUFRLFdBQU8sVUFBVUMsV0FBVixFQUF1QkwsS0FBdkIsRUFBOEJHLEtBQTlCLEVBQXFDQyxVQUFyQyxFQUFpRDtBQUMvRCxZQUFPUCxLQUFLSSxJQUFMLENBQVVILE9BQVYsRUFBbUJPLFdBQW5CLEVBQWdDTCxLQUFoQyxFQUF1Q0csS0FBdkMsRUFBOENDLFVBQTlDLENBQVA7QUFDQSxLQUZPO0FBVlQ7QUFjQSxTQUFPLFlBQVk7QUFDbEIsVUFBT1AsS0FBS1MsS0FBTCxDQUFXUixPQUFYLEVBQW9CUyxTQUFwQixDQUFQO0FBQ0EsR0FGRDtBQUdBLEVBbkJEOztBQXFCQTtBQUNBO0FBQ0E7QUFDQSxLQUFJQyxLQUFLLFNBQUxBLEVBQUssQ0FBVVIsS0FBVixFQUFpQkYsT0FBakIsRUFBMEJDLFFBQTFCLEVBQW9DO0FBQzVDLE1BQUlDLFNBQVMsSUFBYixFQUFtQixPQUFPVixFQUFFbUIsUUFBVDtBQUNuQixNQUFJbkIsRUFBRW9CLFVBQUYsQ0FBYVYsS0FBYixDQUFKLEVBQXlCLE9BQU9KLFdBQVdJLEtBQVgsRUFBa0JGLE9BQWxCLEVBQTJCQyxRQUEzQixDQUFQO0FBQ3pCLE1BQUlULEVBQUVxQixRQUFGLENBQVdYLEtBQVgsQ0FBSixFQUF1QixPQUFPVixFQUFFc0IsT0FBRixDQUFVWixLQUFWLENBQVA7QUFDdkIsU0FBT1YsRUFBRXVCLFFBQUYsQ0FBV2IsS0FBWCxDQUFQO0FBQ0EsRUFMRDtBQU1BVixHQUFFd0IsUUFBRixHQUFhLFVBQVVkLEtBQVYsRUFBaUJGLE9BQWpCLEVBQTBCO0FBQ3RDLFNBQU9VLEdBQUdSLEtBQUgsRUFBVUYsT0FBVixFQUFtQmlCLFFBQW5CLENBQVA7QUFDQSxFQUZEOztBQUlBO0FBQ0EsS0FBSUMsaUJBQWlCLFNBQWpCQSxjQUFpQixDQUFVQyxRQUFWLEVBQW9CQyxhQUFwQixFQUFtQztBQUN2RCxTQUFPLFVBQVUzQixHQUFWLEVBQWU7QUFDckIsT0FBSTRCLFNBQVNaLFVBQVVZLE1BQXZCO0FBQ0EsT0FBSUEsU0FBUyxDQUFULElBQWM1QixPQUFPLElBQXpCLEVBQStCLE9BQU9BLEdBQVA7QUFDL0IsUUFBSyxJQUFJWSxRQUFRLENBQWpCLEVBQW9CQSxRQUFRZ0IsTUFBNUIsRUFBb0NoQixPQUFwQyxFQUE2QztBQUM1QyxRQUFJaUIsU0FBU2IsVUFBVUosS0FBVixDQUFiO0FBQUEsUUFDQ25CLE9BQU9pQyxTQUFTRyxNQUFULENBRFI7QUFBQSxRQUVDQyxJQUFJckMsS0FBS21DLE1BRlY7QUFHQSxTQUFLLElBQUlHLElBQUksQ0FBYixFQUFnQkEsSUFBSUQsQ0FBcEIsRUFBdUJDLEdBQXZCLEVBQTRCO0FBQzNCLFNBQUlDLE1BQU12QyxLQUFLc0MsQ0FBTCxDQUFWO0FBQ0EsU0FBSSxDQUFDSixhQUFELElBQWtCM0IsSUFBSWdDLEdBQUosTUFBYSxLQUFLLENBQXhDLEVBQTJDaEMsSUFBSWdDLEdBQUosSUFBV0gsT0FBT0csR0FBUCxDQUFYO0FBQzNDO0FBQ0Q7QUFDRCxVQUFPaEMsR0FBUDtBQUNBLEdBYkQ7QUFjQSxFQWZEOztBQWlCQTtBQUNBLEtBQUlpQyxhQUFhLFNBQWJBLFVBQWEsQ0FBVXBELFNBQVYsRUFBcUI7QUFDckMsTUFBSSxDQUFDa0IsRUFBRXFCLFFBQUYsQ0FBV3ZDLFNBQVgsQ0FBTCxFQUE0QixPQUFPLEVBQVA7QUFDNUIsTUFBSWUsWUFBSixFQUFrQixPQUFPQSxhQUFhZixTQUFiLENBQVA7QUFDbEJpQixPQUFLakIsU0FBTCxHQUFpQkEsU0FBakI7QUFDQSxNQUFJcUQsU0FBUyxJQUFJcEMsSUFBSixFQUFiO0FBQ0FBLE9BQUtqQixTQUFMLEdBQWlCLElBQWpCO0FBQ0EsU0FBT3FELE1BQVA7QUFDQSxFQVBEOztBQVNBO0FBQ0E7QUFDQTtBQUNBLEtBQUlDLGtCQUFrQkMsS0FBS0MsR0FBTCxDQUFTLENBQVQsRUFBWSxFQUFaLElBQWtCLENBQXhDO0FBQ0EsS0FBSUMsY0FBYyxTQUFkQSxXQUFjLENBQVV6QixVQUFWLEVBQXNCO0FBQ3ZDLE1BQUllLFNBQVNmLGNBQWMsSUFBZCxJQUFzQkEsV0FBV2UsTUFBOUM7QUFDQSxTQUFPLE9BQU9BLE1BQVAsSUFBaUIsUUFBakIsSUFBNkJBLFVBQVUsQ0FBdkMsSUFBNENBLFVBQVVPLGVBQTdEO0FBQ0EsRUFIRDs7QUFLQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTtBQUNBcEMsR0FBRXdDLElBQUYsR0FBU3hDLEVBQUV5QyxPQUFGLEdBQVksVUFBVXhDLEdBQVYsRUFBZXVCLFFBQWYsRUFBeUJoQixPQUF6QixFQUFrQztBQUN0RGdCLGFBQVdsQixXQUFXa0IsUUFBWCxFQUFxQmhCLE9BQXJCLENBQVg7QUFDQSxNQUFJd0IsQ0FBSixFQUFPSCxNQUFQO0FBQ0EsTUFBSVUsWUFBWXRDLEdBQVosQ0FBSixFQUFzQjtBQUNyQixRQUFLK0IsSUFBSSxDQUFKLEVBQU9ILFNBQVM1QixJQUFJNEIsTUFBekIsRUFBaUNHLElBQUlILE1BQXJDLEVBQTZDRyxHQUE3QyxFQUFrRDtBQUNqRFIsYUFBU3ZCLElBQUkrQixDQUFKLENBQVQsRUFBaUJBLENBQWpCLEVBQW9CL0IsR0FBcEI7QUFDQTtBQUNELEdBSkQsTUFJTztBQUNOLE9BQUlQLE9BQU9NLEVBQUVOLElBQUYsQ0FBT08sR0FBUCxDQUFYO0FBQ0EsUUFBSytCLElBQUksQ0FBSixFQUFPSCxTQUFTbkMsS0FBS21DLE1BQTFCLEVBQWtDRyxJQUFJSCxNQUF0QyxFQUE4Q0csR0FBOUMsRUFBbUQ7QUFDbERSLGFBQVN2QixJQUFJUCxLQUFLc0MsQ0FBTCxDQUFKLENBQVQsRUFBdUJ0QyxLQUFLc0MsQ0FBTCxDQUF2QixFQUFnQy9CLEdBQWhDO0FBQ0E7QUFDRDtBQUNELFNBQU9BLEdBQVA7QUFDQSxFQWREOztBQWdCQTtBQUNBRCxHQUFFMEMsR0FBRixHQUFRMUMsRUFBRTJDLE9BQUYsR0FBWSxVQUFVMUMsR0FBVixFQUFldUIsUUFBZixFQUF5QmhCLE9BQXpCLEVBQWtDO0FBQ3JEZ0IsYUFBV04sR0FBR00sUUFBSCxFQUFhaEIsT0FBYixDQUFYO0FBQ0EsTUFBSWQsT0FBTyxDQUFDNkMsWUFBWXRDLEdBQVosQ0FBRCxJQUFxQkQsRUFBRU4sSUFBRixDQUFPTyxHQUFQLENBQWhDO0FBQUEsTUFDQzRCLFNBQVMsQ0FBQ25DLFFBQVFPLEdBQVQsRUFBYzRCLE1BRHhCO0FBQUEsTUFFQ2UsVUFBVS9ELE1BQU1nRCxNQUFOLENBRlg7QUFHQSxPQUFLLElBQUloQixRQUFRLENBQWpCLEVBQW9CQSxRQUFRZ0IsTUFBNUIsRUFBb0NoQixPQUFwQyxFQUE2QztBQUM1QyxPQUFJZ0MsYUFBYW5ELE9BQU9BLEtBQUttQixLQUFMLENBQVAsR0FBcUJBLEtBQXRDO0FBQ0ErQixXQUFRL0IsS0FBUixJQUFpQlcsU0FBU3ZCLElBQUk0QyxVQUFKLENBQVQsRUFBMEJBLFVBQTFCLEVBQXNDNUMsR0FBdEMsQ0FBakI7QUFDQTtBQUNELFNBQU8yQyxPQUFQO0FBQ0EsRUFWRDs7QUFZQTtBQUNBLFVBQVNFLFlBQVQsQ0FBc0JDLEdBQXRCLEVBQTJCO0FBQzFCO0FBQ0E7QUFDQSxXQUFTQyxRQUFULENBQWtCL0MsR0FBbEIsRUFBdUJ1QixRQUF2QixFQUFpQ3lCLElBQWpDLEVBQXVDdkQsSUFBdkMsRUFBNkNtQixLQUE3QyxFQUFvRGdCLE1BQXBELEVBQTREO0FBQzNELFVBQU9oQixTQUFTLENBQVQsSUFBY0EsUUFBUWdCLE1BQTdCLEVBQXFDaEIsU0FBU2tDLEdBQTlDLEVBQW1EO0FBQ2xELFFBQUlGLGFBQWFuRCxPQUFPQSxLQUFLbUIsS0FBTCxDQUFQLEdBQXFCQSxLQUF0QztBQUNBb0MsV0FBT3pCLFNBQVN5QixJQUFULEVBQWVoRCxJQUFJNEMsVUFBSixDQUFmLEVBQWdDQSxVQUFoQyxFQUE0QzVDLEdBQTVDLENBQVA7QUFDQTtBQUNELFVBQU9nRCxJQUFQO0FBQ0E7O0FBRUQsU0FBTyxVQUFVaEQsR0FBVixFQUFldUIsUUFBZixFQUF5QnlCLElBQXpCLEVBQStCekMsT0FBL0IsRUFBd0M7QUFDOUNnQixjQUFXbEIsV0FBV2tCLFFBQVgsRUFBcUJoQixPQUFyQixFQUE4QixDQUE5QixDQUFYO0FBQ0EsT0FBSWQsT0FBTyxDQUFDNkMsWUFBWXRDLEdBQVosQ0FBRCxJQUFxQkQsRUFBRU4sSUFBRixDQUFPTyxHQUFQLENBQWhDO0FBQUEsT0FDQzRCLFNBQVMsQ0FBQ25DLFFBQVFPLEdBQVQsRUFBYzRCLE1BRHhCO0FBQUEsT0FFQ2hCLFFBQVFrQyxNQUFNLENBQU4sR0FBVSxDQUFWLEdBQWNsQixTQUFTLENBRmhDO0FBR0E7QUFDQSxPQUFJWixVQUFVWSxNQUFWLEdBQW1CLENBQXZCLEVBQTBCO0FBQ3pCb0IsV0FBT2hELElBQUlQLE9BQU9BLEtBQUttQixLQUFMLENBQVAsR0FBcUJBLEtBQXpCLENBQVA7QUFDQUEsYUFBU2tDLEdBQVQ7QUFDQTtBQUNELFVBQU9DLFNBQVMvQyxHQUFULEVBQWN1QixRQUFkLEVBQXdCeUIsSUFBeEIsRUFBOEJ2RCxJQUE5QixFQUFvQ21CLEtBQXBDLEVBQTJDZ0IsTUFBM0MsQ0FBUDtBQUNBLEdBWEQ7QUFZQTs7QUFFRDtBQUNBO0FBQ0E3QixHQUFFa0QsTUFBRixHQUFXbEQsRUFBRW1ELEtBQUYsR0FBVW5ELEVBQUVvRCxNQUFGLEdBQVdOLGFBQWEsQ0FBYixDQUFoQzs7QUFFQTtBQUNBOUMsR0FBRXFELFdBQUYsR0FBZ0JyRCxFQUFFc0QsS0FBRixHQUFVUixhQUFhLENBQUMsQ0FBZCxDQUExQjs7QUFFQTtBQUNBOUMsR0FBRXVELElBQUYsR0FBU3ZELEVBQUV3RCxNQUFGLEdBQVcsVUFBVXZELEdBQVYsRUFBZXdELFNBQWYsRUFBMEJqRCxPQUExQixFQUFtQztBQUN0RCxNQUFJeUIsR0FBSjtBQUNBLE1BQUlNLFlBQVl0QyxHQUFaLENBQUosRUFBc0I7QUFDckJnQyxTQUFNakMsRUFBRTBELFNBQUYsQ0FBWXpELEdBQVosRUFBaUJ3RCxTQUFqQixFQUE0QmpELE9BQTVCLENBQU47QUFDQSxHQUZELE1BRU87QUFDTnlCLFNBQU1qQyxFQUFFMkQsT0FBRixDQUFVMUQsR0FBVixFQUFld0QsU0FBZixFQUEwQmpELE9BQTFCLENBQU47QUFDQTtBQUNELE1BQUl5QixRQUFRLEtBQUssQ0FBYixJQUFrQkEsUUFBUSxDQUFDLENBQS9CLEVBQWtDLE9BQU9oQyxJQUFJZ0MsR0FBSixDQUFQO0FBQ2xDLEVBUkQ7O0FBVUE7QUFDQTtBQUNBakMsR0FBRTRELE1BQUYsR0FBVzVELEVBQUU2RCxNQUFGLEdBQVcsVUFBVTVELEdBQVYsRUFBZXdELFNBQWYsRUFBMEJqRCxPQUExQixFQUFtQztBQUN4RCxNQUFJb0MsVUFBVSxFQUFkO0FBQ0FhLGNBQVl2QyxHQUFHdUMsU0FBSCxFQUFjakQsT0FBZCxDQUFaO0FBQ0FSLElBQUV3QyxJQUFGLENBQU92QyxHQUFQLEVBQVksVUFBVVMsS0FBVixFQUFpQkcsS0FBakIsRUFBd0JpRCxJQUF4QixFQUE4QjtBQUN6QyxPQUFJTCxVQUFVL0MsS0FBVixFQUFpQkcsS0FBakIsRUFBd0JpRCxJQUF4QixDQUFKLEVBQW1DbEIsUUFBUXpELElBQVIsQ0FBYXVCLEtBQWI7QUFDbkMsR0FGRDtBQUdBLFNBQU9rQyxPQUFQO0FBQ0EsRUFQRDs7QUFTQTtBQUNBNUMsR0FBRStELE1BQUYsR0FBVyxVQUFVOUQsR0FBVixFQUFld0QsU0FBZixFQUEwQmpELE9BQTFCLEVBQW1DO0FBQzdDLFNBQU9SLEVBQUU0RCxNQUFGLENBQVMzRCxHQUFULEVBQWNELEVBQUVnRSxNQUFGLENBQVM5QyxHQUFHdUMsU0FBSCxDQUFULENBQWQsRUFBdUNqRCxPQUF2QyxDQUFQO0FBQ0EsRUFGRDs7QUFJQTtBQUNBO0FBQ0FSLEdBQUVpRSxLQUFGLEdBQVVqRSxFQUFFa0UsR0FBRixHQUFRLFVBQVVqRSxHQUFWLEVBQWV3RCxTQUFmLEVBQTBCakQsT0FBMUIsRUFBbUM7QUFDcERpRCxjQUFZdkMsR0FBR3VDLFNBQUgsRUFBY2pELE9BQWQsQ0FBWjtBQUNBLE1BQUlkLE9BQU8sQ0FBQzZDLFlBQVl0QyxHQUFaLENBQUQsSUFBcUJELEVBQUVOLElBQUYsQ0FBT08sR0FBUCxDQUFoQztBQUFBLE1BQ0M0QixTQUFTLENBQUNuQyxRQUFRTyxHQUFULEVBQWM0QixNQUR4QjtBQUVBLE9BQUssSUFBSWhCLFFBQVEsQ0FBakIsRUFBb0JBLFFBQVFnQixNQUE1QixFQUFvQ2hCLE9BQXBDLEVBQTZDO0FBQzVDLE9BQUlnQyxhQUFhbkQsT0FBT0EsS0FBS21CLEtBQUwsQ0FBUCxHQUFxQkEsS0FBdEM7QUFDQSxPQUFJLENBQUM0QyxVQUFVeEQsSUFBSTRDLFVBQUosQ0FBVixFQUEyQkEsVUFBM0IsRUFBdUM1QyxHQUF2QyxDQUFMLEVBQWtELE9BQU8sS0FBUDtBQUNsRDtBQUNELFNBQU8sSUFBUDtBQUNBLEVBVEQ7O0FBV0E7QUFDQTtBQUNBRCxHQUFFbUUsSUFBRixHQUFTbkUsRUFBRW9FLEdBQUYsR0FBUSxVQUFVbkUsR0FBVixFQUFld0QsU0FBZixFQUEwQmpELE9BQTFCLEVBQW1DO0FBQ25EaUQsY0FBWXZDLEdBQUd1QyxTQUFILEVBQWNqRCxPQUFkLENBQVo7QUFDQSxNQUFJZCxPQUFPLENBQUM2QyxZQUFZdEMsR0FBWixDQUFELElBQXFCRCxFQUFFTixJQUFGLENBQU9PLEdBQVAsQ0FBaEM7QUFBQSxNQUNDNEIsU0FBUyxDQUFDbkMsUUFBUU8sR0FBVCxFQUFjNEIsTUFEeEI7QUFFQSxPQUFLLElBQUloQixRQUFRLENBQWpCLEVBQW9CQSxRQUFRZ0IsTUFBNUIsRUFBb0NoQixPQUFwQyxFQUE2QztBQUM1QyxPQUFJZ0MsYUFBYW5ELE9BQU9BLEtBQUttQixLQUFMLENBQVAsR0FBcUJBLEtBQXRDO0FBQ0EsT0FBSTRDLFVBQVV4RCxJQUFJNEMsVUFBSixDQUFWLEVBQTJCQSxVQUEzQixFQUF1QzVDLEdBQXZDLENBQUosRUFBaUQsT0FBTyxJQUFQO0FBQ2pEO0FBQ0QsU0FBTyxLQUFQO0FBQ0EsRUFURDs7QUFXQTtBQUNBO0FBQ0FELEdBQUVxRSxRQUFGLEdBQWFyRSxFQUFFc0UsUUFBRixHQUFhdEUsRUFBRXVFLE9BQUYsR0FBWSxVQUFVdEUsR0FBVixFQUFldUUsTUFBZixFQUF1QkMsU0FBdkIsRUFBa0M7QUFDdkUsTUFBSSxDQUFDbEMsWUFBWXRDLEdBQVosQ0FBTCxFQUF1QkEsTUFBTUQsRUFBRTBFLE1BQUYsQ0FBU3pFLEdBQVQsQ0FBTjtBQUN2QixTQUFPRCxFQUFFMkUsT0FBRixDQUFVMUUsR0FBVixFQUFldUUsTUFBZixFQUF1QixPQUFPQyxTQUFQLElBQW9CLFFBQXBCLElBQWdDQSxTQUF2RCxLQUFxRSxDQUE1RTtBQUNBLEVBSEQ7O0FBS0E7QUFDQXpFLEdBQUU0RSxNQUFGLEdBQVcsVUFBVTNFLEdBQVYsRUFBZTRFLE1BQWYsRUFBdUI7QUFDakMsTUFBSUMsT0FBTzFGLE1BQU11QixJQUFOLENBQVdNLFNBQVgsRUFBc0IsQ0FBdEIsQ0FBWDtBQUNBLE1BQUk4RCxTQUFTL0UsRUFBRW9CLFVBQUYsQ0FBYXlELE1BQWIsQ0FBYjtBQUNBLFNBQU83RSxFQUFFMEMsR0FBRixDQUFNekMsR0FBTixFQUFXLFVBQVVTLEtBQVYsRUFBaUI7QUFDbEMsT0FBSUgsT0FBT3dFLFNBQVNGLE1BQVQsR0FBa0JuRSxNQUFNbUUsTUFBTixDQUE3QjtBQUNBLFVBQU90RSxRQUFRLElBQVIsR0FBZUEsSUFBZixHQUFzQkEsS0FBS1MsS0FBTCxDQUFXTixLQUFYLEVBQWtCb0UsSUFBbEIsQ0FBN0I7QUFDQSxHQUhNLENBQVA7QUFJQSxFQVBEOztBQVNBO0FBQ0E5RSxHQUFFZ0YsS0FBRixHQUFVLFVBQVUvRSxHQUFWLEVBQWVnQyxHQUFmLEVBQW9CO0FBQzdCLFNBQU9qQyxFQUFFMEMsR0FBRixDQUFNekMsR0FBTixFQUFXRCxFQUFFdUIsUUFBRixDQUFXVSxHQUFYLENBQVgsQ0FBUDtBQUNBLEVBRkQ7O0FBSUE7QUFDQTtBQUNBakMsR0FBRWlGLEtBQUYsR0FBVSxVQUFVaEYsR0FBVixFQUFlaUYsS0FBZixFQUFzQjtBQUMvQixTQUFPbEYsRUFBRTRELE1BQUYsQ0FBUzNELEdBQVQsRUFBY0QsRUFBRXNCLE9BQUYsQ0FBVTRELEtBQVYsQ0FBZCxDQUFQO0FBQ0EsRUFGRDs7QUFJQTtBQUNBO0FBQ0FsRixHQUFFbUYsU0FBRixHQUFjLFVBQVVsRixHQUFWLEVBQWVpRixLQUFmLEVBQXNCO0FBQ25DLFNBQU9sRixFQUFFdUQsSUFBRixDQUFPdEQsR0FBUCxFQUFZRCxFQUFFc0IsT0FBRixDQUFVNEQsS0FBVixDQUFaLENBQVA7QUFDQSxFQUZEOztBQUlBO0FBQ0FsRixHQUFFb0YsR0FBRixHQUFRLFVBQVVuRixHQUFWLEVBQWV1QixRQUFmLEVBQXlCaEIsT0FBekIsRUFBa0M7QUFDekMsTUFBSTJCLFNBQVMsQ0FBQ1YsUUFBZDtBQUFBLE1BQXdCNEQsZUFBZSxDQUFDNUQsUUFBeEM7QUFBQSxNQUNDZixLQUREO0FBQUEsTUFDUTRFLFFBRFI7QUFFQSxNQUFJOUQsWUFBWSxJQUFaLElBQW9CdkIsT0FBTyxJQUEvQixFQUFxQztBQUNwQ0EsU0FBTXNDLFlBQVl0QyxHQUFaLElBQW1CQSxHQUFuQixHQUF5QkQsRUFBRTBFLE1BQUYsQ0FBU3pFLEdBQVQsQ0FBL0I7QUFDQSxRQUFLLElBQUkrQixJQUFJLENBQVIsRUFBV0gsU0FBUzVCLElBQUk0QixNQUE3QixFQUFxQ0csSUFBSUgsTUFBekMsRUFBaURHLEdBQWpELEVBQXNEO0FBQ3JEdEIsWUFBUVQsSUFBSStCLENBQUosQ0FBUjtBQUNBLFFBQUl0QixRQUFReUIsTUFBWixFQUFvQjtBQUNuQkEsY0FBU3pCLEtBQVQ7QUFDQTtBQUNEO0FBQ0QsR0FSRCxNQVFPO0FBQ05jLGNBQVdOLEdBQUdNLFFBQUgsRUFBYWhCLE9BQWIsQ0FBWDtBQUNBUixLQUFFd0MsSUFBRixDQUFPdkMsR0FBUCxFQUFZLFVBQVVTLEtBQVYsRUFBaUJHLEtBQWpCLEVBQXdCaUQsSUFBeEIsRUFBOEI7QUFDekN3QixlQUFXOUQsU0FBU2QsS0FBVCxFQUFnQkcsS0FBaEIsRUFBdUJpRCxJQUF2QixDQUFYO0FBQ0EsUUFBSXdCLFdBQVdELFlBQVgsSUFBMkJDLGFBQWEsQ0FBQzdELFFBQWQsSUFBMEJVLFdBQVcsQ0FBQ1YsUUFBckUsRUFBK0U7QUFDOUVVLGNBQVN6QixLQUFUO0FBQ0EyRSxvQkFBZUMsUUFBZjtBQUNBO0FBQ0QsSUFORDtBQU9BO0FBQ0QsU0FBT25ELE1BQVA7QUFDQSxFQXRCRDs7QUF3QkE7QUFDQW5DLEdBQUV1RixHQUFGLEdBQVEsVUFBVXRGLEdBQVYsRUFBZXVCLFFBQWYsRUFBeUJoQixPQUF6QixFQUFrQztBQUN6QyxNQUFJMkIsU0FBU1YsUUFBYjtBQUFBLE1BQXVCNEQsZUFBZTVELFFBQXRDO0FBQUEsTUFDQ2YsS0FERDtBQUFBLE1BQ1E0RSxRQURSO0FBRUEsTUFBSTlELFlBQVksSUFBWixJQUFvQnZCLE9BQU8sSUFBL0IsRUFBcUM7QUFDcENBLFNBQU1zQyxZQUFZdEMsR0FBWixJQUFtQkEsR0FBbkIsR0FBeUJELEVBQUUwRSxNQUFGLENBQVN6RSxHQUFULENBQS9CO0FBQ0EsUUFBSyxJQUFJK0IsSUFBSSxDQUFSLEVBQVdILFNBQVM1QixJQUFJNEIsTUFBN0IsRUFBcUNHLElBQUlILE1BQXpDLEVBQWlERyxHQUFqRCxFQUFzRDtBQUNyRHRCLFlBQVFULElBQUkrQixDQUFKLENBQVI7QUFDQSxRQUFJdEIsUUFBUXlCLE1BQVosRUFBb0I7QUFDbkJBLGNBQVN6QixLQUFUO0FBQ0E7QUFDRDtBQUNELEdBUkQsTUFRTztBQUNOYyxjQUFXTixHQUFHTSxRQUFILEVBQWFoQixPQUFiLENBQVg7QUFDQVIsS0FBRXdDLElBQUYsQ0FBT3ZDLEdBQVAsRUFBWSxVQUFVUyxLQUFWLEVBQWlCRyxLQUFqQixFQUF3QmlELElBQXhCLEVBQThCO0FBQ3pDd0IsZUFBVzlELFNBQVNkLEtBQVQsRUFBZ0JHLEtBQWhCLEVBQXVCaUQsSUFBdkIsQ0FBWDtBQUNBLFFBQUl3QixXQUFXRCxZQUFYLElBQTJCQyxhQUFhN0QsUUFBYixJQUF5QlUsV0FBV1YsUUFBbkUsRUFBNkU7QUFDNUVVLGNBQVN6QixLQUFUO0FBQ0EyRSxvQkFBZUMsUUFBZjtBQUNBO0FBQ0QsSUFORDtBQU9BO0FBQ0QsU0FBT25ELE1BQVA7QUFDQSxFQXRCRDs7QUF3QkE7QUFDQTtBQUNBbkMsR0FBRXdGLE9BQUYsR0FBWSxVQUFVdkYsR0FBVixFQUFlO0FBQzFCLE1BQUl3RixNQUFNbEQsWUFBWXRDLEdBQVosSUFBbUJBLEdBQW5CLEdBQXlCRCxFQUFFMEUsTUFBRixDQUFTekUsR0FBVCxDQUFuQztBQUNBLE1BQUk0QixTQUFTNEQsSUFBSTVELE1BQWpCO0FBQ0EsTUFBSTZELFdBQVc3RyxNQUFNZ0QsTUFBTixDQUFmO0FBQ0EsT0FBSyxJQUFJaEIsUUFBUSxDQUFaLEVBQWU4RSxJQUFwQixFQUEwQjlFLFFBQVFnQixNQUFsQyxFQUEwQ2hCLE9BQTFDLEVBQW1EO0FBQ2xEOEUsVUFBTzNGLEVBQUU0RixNQUFGLENBQVMsQ0FBVCxFQUFZL0UsS0FBWixDQUFQO0FBQ0EsT0FBSThFLFNBQVM5RSxLQUFiLEVBQW9CNkUsU0FBUzdFLEtBQVQsSUFBa0I2RSxTQUFTQyxJQUFULENBQWxCO0FBQ3BCRCxZQUFTQyxJQUFULElBQWlCRixJQUFJNUUsS0FBSixDQUFqQjtBQUNBO0FBQ0QsU0FBTzZFLFFBQVA7QUFDQSxFQVZEOztBQVlBO0FBQ0E7QUFDQTtBQUNBMUYsR0FBRTZGLE1BQUYsR0FBVyxVQUFVNUYsR0FBVixFQUFlNkYsQ0FBZixFQUFrQkMsS0FBbEIsRUFBeUI7QUFDbkMsTUFBSUQsS0FBSyxJQUFMLElBQWFDLEtBQWpCLEVBQXdCO0FBQ3ZCLE9BQUksQ0FBQ3hELFlBQVl0QyxHQUFaLENBQUwsRUFBdUJBLE1BQU1ELEVBQUUwRSxNQUFGLENBQVN6RSxHQUFULENBQU47QUFDdkIsVUFBT0EsSUFBSUQsRUFBRTRGLE1BQUYsQ0FBUzNGLElBQUk0QixNQUFKLEdBQWEsQ0FBdEIsQ0FBSixDQUFQO0FBQ0E7QUFDRCxTQUFPN0IsRUFBRXdGLE9BQUYsQ0FBVXZGLEdBQVYsRUFBZWIsS0FBZixDQUFxQixDQUFyQixFQUF3QmlELEtBQUsrQyxHQUFMLENBQVMsQ0FBVCxFQUFZVSxDQUFaLENBQXhCLENBQVA7QUFDQSxFQU5EOztBQVFBO0FBQ0E5RixHQUFFZ0csTUFBRixHQUFXLFVBQVUvRixHQUFWLEVBQWV1QixRQUFmLEVBQXlCaEIsT0FBekIsRUFBa0M7QUFDNUNnQixhQUFXTixHQUFHTSxRQUFILEVBQWFoQixPQUFiLENBQVg7QUFDQSxTQUFPUixFQUFFZ0YsS0FBRixDQUFRaEYsRUFBRTBDLEdBQUYsQ0FBTXpDLEdBQU4sRUFBVyxVQUFVUyxLQUFWLEVBQWlCRyxLQUFqQixFQUF3QmlELElBQXhCLEVBQThCO0FBQ3ZELFVBQU87QUFDTnBELFdBQU9BLEtBREQ7QUFFTkcsV0FBT0EsS0FGRDtBQUdOb0YsY0FBVXpFLFNBQVNkLEtBQVQsRUFBZ0JHLEtBQWhCLEVBQXVCaUQsSUFBdkI7QUFISixJQUFQO0FBS0EsR0FOYyxFQU1ab0MsSUFOWSxDQU1QLFVBQVVDLElBQVYsRUFBZ0JDLEtBQWhCLEVBQXVCO0FBQzlCLE9BQUlDLElBQUlGLEtBQUtGLFFBQWI7QUFDQSxPQUFJSyxJQUFJRixNQUFNSCxRQUFkO0FBQ0EsT0FBSUksTUFBTUMsQ0FBVixFQUFhO0FBQ1osUUFBSUQsSUFBSUMsQ0FBSixJQUFTRCxNQUFNLEtBQUssQ0FBeEIsRUFBMkIsT0FBTyxDQUFQO0FBQzNCLFFBQUlBLElBQUlDLENBQUosSUFBU0EsTUFBTSxLQUFLLENBQXhCLEVBQTJCLE9BQU8sQ0FBQyxDQUFSO0FBQzNCO0FBQ0QsVUFBT0gsS0FBS3RGLEtBQUwsR0FBYXVGLE1BQU12RixLQUExQjtBQUNBLEdBZGMsQ0FBUixFQWNILE9BZEcsQ0FBUDtBQWVBLEVBakJEOztBQW1CQTtBQUNBLEtBQUkwRixRQUFRLFNBQVJBLEtBQVEsQ0FBVUMsUUFBVixFQUFvQjtBQUMvQixTQUFPLFVBQVV2RyxHQUFWLEVBQWV1QixRQUFmLEVBQXlCaEIsT0FBekIsRUFBa0M7QUFDeEMsT0FBSTJCLFNBQVMsRUFBYjtBQUNBWCxjQUFXTixHQUFHTSxRQUFILEVBQWFoQixPQUFiLENBQVg7QUFDQVIsS0FBRXdDLElBQUYsQ0FBT3ZDLEdBQVAsRUFBWSxVQUFVUyxLQUFWLEVBQWlCRyxLQUFqQixFQUF3QjtBQUNuQyxRQUFJb0IsTUFBTVQsU0FBU2QsS0FBVCxFQUFnQkcsS0FBaEIsRUFBdUJaLEdBQXZCLENBQVY7QUFDQXVHLGFBQVNyRSxNQUFULEVBQWlCekIsS0FBakIsRUFBd0J1QixHQUF4QjtBQUNBLElBSEQ7QUFJQSxVQUFPRSxNQUFQO0FBQ0EsR0FSRDtBQVNBLEVBVkQ7O0FBWUE7QUFDQTtBQUNBbkMsR0FBRXlHLE9BQUYsR0FBWUYsTUFBTSxVQUFVcEUsTUFBVixFQUFrQnpCLEtBQWxCLEVBQXlCdUIsR0FBekIsRUFBOEI7QUFDL0MsTUFBSWpDLEVBQUUwRyxHQUFGLENBQU12RSxNQUFOLEVBQWNGLEdBQWQsQ0FBSixFQUF3QkUsT0FBT0YsR0FBUCxFQUFZOUMsSUFBWixDQUFpQnVCLEtBQWpCLEVBQXhCLEtBQXNEeUIsT0FBT0YsR0FBUCxJQUFjLENBQUN2QixLQUFELENBQWQ7QUFDdEQsRUFGVyxDQUFaOztBQUlBO0FBQ0E7QUFDQVYsR0FBRTJHLE9BQUYsR0FBWUosTUFBTSxVQUFVcEUsTUFBVixFQUFrQnpCLEtBQWxCLEVBQXlCdUIsR0FBekIsRUFBOEI7QUFDL0NFLFNBQU9GLEdBQVAsSUFBY3ZCLEtBQWQ7QUFDQSxFQUZXLENBQVo7O0FBSUE7QUFDQTtBQUNBO0FBQ0FWLEdBQUU0RyxPQUFGLEdBQVlMLE1BQU0sVUFBVXBFLE1BQVYsRUFBa0J6QixLQUFsQixFQUF5QnVCLEdBQXpCLEVBQThCO0FBQy9DLE1BQUlqQyxFQUFFMEcsR0FBRixDQUFNdkUsTUFBTixFQUFjRixHQUFkLENBQUosRUFBd0JFLE9BQU9GLEdBQVAsSUFBeEIsS0FBNENFLE9BQU9GLEdBQVAsSUFBYyxDQUFkO0FBQzVDLEVBRlcsQ0FBWjs7QUFJQTtBQUNBakMsR0FBRTZHLE9BQUYsR0FBWSxVQUFVNUcsR0FBVixFQUFlO0FBQzFCLE1BQUksQ0FBQ0EsR0FBTCxFQUFVLE9BQU8sRUFBUDtBQUNWLE1BQUlELEVBQUVSLE9BQUYsQ0FBVVMsR0FBVixDQUFKLEVBQW9CLE9BQU9iLE1BQU11QixJQUFOLENBQVdWLEdBQVgsQ0FBUDtBQUNwQixNQUFJc0MsWUFBWXRDLEdBQVosQ0FBSixFQUFzQixPQUFPRCxFQUFFMEMsR0FBRixDQUFNekMsR0FBTixFQUFXRCxFQUFFbUIsUUFBYixDQUFQO0FBQ3RCLFNBQU9uQixFQUFFMEUsTUFBRixDQUFTekUsR0FBVCxDQUFQO0FBQ0EsRUFMRDs7QUFPQTtBQUNBRCxHQUFFOEcsSUFBRixHQUFTLFVBQVU3RyxHQUFWLEVBQWU7QUFDdkIsTUFBSUEsT0FBTyxJQUFYLEVBQWlCLE9BQU8sQ0FBUDtBQUNqQixTQUFPc0MsWUFBWXRDLEdBQVosSUFBbUJBLElBQUk0QixNQUF2QixHQUFnQzdCLEVBQUVOLElBQUYsQ0FBT08sR0FBUCxFQUFZNEIsTUFBbkQ7QUFDQSxFQUhEOztBQUtBO0FBQ0E7QUFDQTdCLEdBQUUrRyxTQUFGLEdBQWMsVUFBVTlHLEdBQVYsRUFBZXdELFNBQWYsRUFBMEJqRCxPQUExQixFQUFtQztBQUNoRGlELGNBQVl2QyxHQUFHdUMsU0FBSCxFQUFjakQsT0FBZCxDQUFaO0FBQ0EsTUFBSXdHLE9BQU8sRUFBWDtBQUFBLE1BQWVDLE9BQU8sRUFBdEI7QUFDQWpILElBQUV3QyxJQUFGLENBQU92QyxHQUFQLEVBQVksVUFBVVMsS0FBVixFQUFpQnVCLEdBQWpCLEVBQXNCaEMsR0FBdEIsRUFBMkI7QUFDdEMsSUFBQ3dELFVBQVUvQyxLQUFWLEVBQWlCdUIsR0FBakIsRUFBc0JoQyxHQUF0QixJQUE2QitHLElBQTdCLEdBQW9DQyxJQUFyQyxFQUEyQzlILElBQTNDLENBQWdEdUIsS0FBaEQ7QUFDQSxHQUZEO0FBR0EsU0FBTyxDQUFDc0csSUFBRCxFQUFPQyxJQUFQLENBQVA7QUFDQSxFQVBEOztBQVNBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0FqSCxHQUFFa0gsS0FBRixHQUFVbEgsRUFBRW1ILElBQUYsR0FBU25ILEVBQUVvSCxJQUFGLEdBQVMsVUFBVUMsS0FBVixFQUFpQnZCLENBQWpCLEVBQW9CQyxLQUFwQixFQUEyQjtBQUN0RCxNQUFJc0IsU0FBUyxJQUFiLEVBQW1CLE9BQU8sS0FBSyxDQUFaO0FBQ25CLE1BQUl2QixLQUFLLElBQUwsSUFBYUMsS0FBakIsRUFBd0IsT0FBT3NCLE1BQU0sQ0FBTixDQUFQO0FBQ3hCLFNBQU9ySCxFQUFFc0gsT0FBRixDQUFVRCxLQUFWLEVBQWlCQSxNQUFNeEYsTUFBTixHQUFlaUUsQ0FBaEMsQ0FBUDtBQUNBLEVBSkQ7O0FBTUE7QUFDQTtBQUNBO0FBQ0E5RixHQUFFc0gsT0FBRixHQUFZLFVBQVVELEtBQVYsRUFBaUJ2QixDQUFqQixFQUFvQkMsS0FBcEIsRUFBMkI7QUFDdEMsU0FBTzNHLE1BQU11QixJQUFOLENBQVcwRyxLQUFYLEVBQWtCLENBQWxCLEVBQXFCaEYsS0FBSytDLEdBQUwsQ0FBUyxDQUFULEVBQVlpQyxNQUFNeEYsTUFBTixJQUFnQmlFLEtBQUssSUFBTCxJQUFhQyxLQUFiLEdBQXFCLENBQXJCLEdBQXlCRCxDQUF6QyxDQUFaLENBQXJCLENBQVA7QUFDQSxFQUZEOztBQUlBO0FBQ0E7QUFDQTlGLEdBQUV1SCxJQUFGLEdBQVMsVUFBVUYsS0FBVixFQUFpQnZCLENBQWpCLEVBQW9CQyxLQUFwQixFQUEyQjtBQUNuQyxNQUFJc0IsU0FBUyxJQUFiLEVBQW1CLE9BQU8sS0FBSyxDQUFaO0FBQ25CLE1BQUl2QixLQUFLLElBQUwsSUFBYUMsS0FBakIsRUFBd0IsT0FBT3NCLE1BQU1BLE1BQU14RixNQUFOLEdBQWUsQ0FBckIsQ0FBUDtBQUN4QixTQUFPN0IsRUFBRXdILElBQUYsQ0FBT0gsS0FBUCxFQUFjaEYsS0FBSytDLEdBQUwsQ0FBUyxDQUFULEVBQVlpQyxNQUFNeEYsTUFBTixHQUFlaUUsQ0FBM0IsQ0FBZCxDQUFQO0FBQ0EsRUFKRDs7QUFNQTtBQUNBO0FBQ0E7QUFDQTlGLEdBQUV3SCxJQUFGLEdBQVN4SCxFQUFFeUgsSUFBRixHQUFTekgsRUFBRTBILElBQUYsR0FBUyxVQUFVTCxLQUFWLEVBQWlCdkIsQ0FBakIsRUFBb0JDLEtBQXBCLEVBQTJCO0FBQ3JELFNBQU8zRyxNQUFNdUIsSUFBTixDQUFXMEcsS0FBWCxFQUFrQnZCLEtBQUssSUFBTCxJQUFhQyxLQUFiLEdBQXFCLENBQXJCLEdBQXlCRCxDQUEzQyxDQUFQO0FBQ0EsRUFGRDs7QUFJQTtBQUNBOUYsR0FBRTJILE9BQUYsR0FBWSxVQUFVTixLQUFWLEVBQWlCO0FBQzVCLFNBQU9ySCxFQUFFNEQsTUFBRixDQUFTeUQsS0FBVCxFQUFnQnJILEVBQUVtQixRQUFsQixDQUFQO0FBQ0EsRUFGRDs7QUFJQTtBQUNBLEtBQUl5RyxVQUFVLFNBQVZBLE9BQVUsQ0FBVUMsS0FBVixFQUFpQkMsT0FBakIsRUFBMEJDLE1BQTFCLEVBQWtDQyxVQUFsQyxFQUE4QztBQUMzRCxNQUFJQyxTQUFTLEVBQWI7QUFBQSxNQUFpQkMsTUFBTSxDQUF2QjtBQUNBLE9BQUssSUFBSWxHLElBQUlnRyxjQUFjLENBQXRCLEVBQXlCbkcsU0FBU2dHLFNBQVNBLE1BQU1oRyxNQUF0RCxFQUE4REcsSUFBSUgsTUFBbEUsRUFBMEVHLEdBQTFFLEVBQStFO0FBQzlFLE9BQUl0QixRQUFRbUgsTUFBTTdGLENBQU4sQ0FBWjtBQUNBLE9BQUlPLFlBQVk3QixLQUFaLE1BQXVCVixFQUFFUixPQUFGLENBQVVrQixLQUFWLEtBQW9CVixFQUFFbUksV0FBRixDQUFjekgsS0FBZCxDQUEzQyxDQUFKLEVBQXNFO0FBQ3JFO0FBQ0EsUUFBSSxDQUFDb0gsT0FBTCxFQUFjcEgsUUFBUWtILFFBQVFsSCxLQUFSLEVBQWVvSCxPQUFmLEVBQXdCQyxNQUF4QixDQUFSO0FBQ2QsUUFBSUssSUFBSSxDQUFSO0FBQUEsUUFBV0MsTUFBTTNILE1BQU1tQixNQUF2QjtBQUNBb0csV0FBT3BHLE1BQVAsSUFBaUJ3RyxHQUFqQjtBQUNBLFdBQU9ELElBQUlDLEdBQVgsRUFBZ0I7QUFDZkosWUFBT0MsS0FBUCxJQUFnQnhILE1BQU0wSCxHQUFOLENBQWhCO0FBQ0E7QUFDRCxJQVJELE1BUU8sSUFBSSxDQUFDTCxNQUFMLEVBQWE7QUFDbkJFLFdBQU9DLEtBQVAsSUFBZ0J4SCxLQUFoQjtBQUNBO0FBQ0Q7QUFDRCxTQUFPdUgsTUFBUDtBQUNBLEVBakJEOztBQW1CQTtBQUNBakksR0FBRTRILE9BQUYsR0FBWSxVQUFVUCxLQUFWLEVBQWlCUyxPQUFqQixFQUEwQjtBQUNyQyxTQUFPRixRQUFRUCxLQUFSLEVBQWVTLE9BQWYsRUFBd0IsS0FBeEIsQ0FBUDtBQUNBLEVBRkQ7O0FBSUE7QUFDQTlILEdBQUVzSSxPQUFGLEdBQVksVUFBVWpCLEtBQVYsRUFBaUI7QUFDNUIsU0FBT3JILEVBQUV1SSxVQUFGLENBQWFsQixLQUFiLEVBQW9CakksTUFBTXVCLElBQU4sQ0FBV00sU0FBWCxFQUFzQixDQUF0QixDQUFwQixDQUFQO0FBQ0EsRUFGRDs7QUFJQTtBQUNBO0FBQ0E7QUFDQWpCLEdBQUV3SSxJQUFGLEdBQVN4SSxFQUFFeUksTUFBRixHQUFXLFVBQVVwQixLQUFWLEVBQWlCcUIsUUFBakIsRUFBMkJsSCxRQUEzQixFQUFxQ2hCLE9BQXJDLEVBQThDO0FBQ2pFLE1BQUk2RyxTQUFTLElBQWIsRUFBbUIsT0FBTyxFQUFQO0FBQ25CLE1BQUksQ0FBQ3JILEVBQUUySSxTQUFGLENBQVlELFFBQVosQ0FBTCxFQUE0QjtBQUMzQmxJLGFBQVVnQixRQUFWO0FBQ0FBLGNBQVdrSCxRQUFYO0FBQ0FBLGNBQVcsS0FBWDtBQUNBO0FBQ0QsTUFBSWxILFlBQVksSUFBaEIsRUFBc0JBLFdBQVdOLEdBQUdNLFFBQUgsRUFBYWhCLE9BQWIsQ0FBWDtBQUN0QixNQUFJMkIsU0FBUyxFQUFiO0FBQ0EsTUFBSXlHLE9BQU8sRUFBWDtBQUNBLE9BQUssSUFBSTVHLElBQUksQ0FBUixFQUFXSCxTQUFTd0YsTUFBTXhGLE1BQS9CLEVBQXVDRyxJQUFJSCxNQUEzQyxFQUFtREcsR0FBbkQsRUFBd0Q7QUFDdkQsT0FBSXRCLFFBQVEyRyxNQUFNckYsQ0FBTixDQUFaO0FBQUEsT0FDQ3NELFdBQVc5RCxXQUFXQSxTQUFTZCxLQUFULEVBQWdCc0IsQ0FBaEIsRUFBbUJxRixLQUFuQixDQUFYLEdBQXVDM0csS0FEbkQ7QUFFQSxPQUFJZ0ksUUFBSixFQUFjO0FBQ2IsUUFBSSxDQUFDMUcsQ0FBRCxJQUFNNEcsU0FBU3RELFFBQW5CLEVBQTZCbkQsT0FBT2hELElBQVAsQ0FBWXVCLEtBQVo7QUFDN0JrSSxXQUFPdEQsUUFBUDtBQUNBLElBSEQsTUFHTyxJQUFJOUQsUUFBSixFQUFjO0FBQ3BCLFFBQUksQ0FBQ3hCLEVBQUVxRSxRQUFGLENBQVd1RSxJQUFYLEVBQWlCdEQsUUFBakIsQ0FBTCxFQUFpQztBQUNoQ3NELFVBQUt6SixJQUFMLENBQVVtRyxRQUFWO0FBQ0FuRCxZQUFPaEQsSUFBUCxDQUFZdUIsS0FBWjtBQUNBO0FBQ0QsSUFMTSxNQUtBLElBQUksQ0FBQ1YsRUFBRXFFLFFBQUYsQ0FBV2xDLE1BQVgsRUFBbUJ6QixLQUFuQixDQUFMLEVBQWdDO0FBQ3RDeUIsV0FBT2hELElBQVAsQ0FBWXVCLEtBQVo7QUFDQTtBQUNEO0FBQ0QsU0FBT3lCLE1BQVA7QUFDQSxFQTFCRDs7QUE0QkE7QUFDQTtBQUNBbkMsR0FBRTZJLEtBQUYsR0FBVSxZQUFZO0FBQ3JCLFNBQU83SSxFQUFFd0ksSUFBRixDQUFPWixRQUFRM0csU0FBUixFQUFtQixJQUFuQixFQUF5QixJQUF6QixDQUFQLENBQVA7QUFDQSxFQUZEOztBQUlBO0FBQ0E7QUFDQWpCLEdBQUU4SSxZQUFGLEdBQWlCLFVBQVV6QixLQUFWLEVBQWlCO0FBQ2pDLE1BQUlBLFNBQVMsSUFBYixFQUFtQixPQUFPLEVBQVA7QUFDbkIsTUFBSWxGLFNBQVMsRUFBYjtBQUNBLE1BQUk0RyxhQUFhOUgsVUFBVVksTUFBM0I7QUFDQSxPQUFLLElBQUlHLElBQUksQ0FBUixFQUFXSCxTQUFTd0YsTUFBTXhGLE1BQS9CLEVBQXVDRyxJQUFJSCxNQUEzQyxFQUFtREcsR0FBbkQsRUFBd0Q7QUFDdkQsT0FBSWdILE9BQU8zQixNQUFNckYsQ0FBTixDQUFYO0FBQ0EsT0FBSWhDLEVBQUVxRSxRQUFGLENBQVdsQyxNQUFYLEVBQW1CNkcsSUFBbkIsQ0FBSixFQUE4QjtBQUM5QixRQUFLLElBQUlaLElBQUksQ0FBYixFQUFnQkEsSUFBSVcsVUFBcEIsRUFBZ0NYLEdBQWhDLEVBQXFDO0FBQ3BDLFFBQUksQ0FBQ3BJLEVBQUVxRSxRQUFGLENBQVdwRCxVQUFVbUgsQ0FBVixDQUFYLEVBQXlCWSxJQUF6QixDQUFMLEVBQXFDO0FBQ3JDO0FBQ0QsT0FBSVosTUFBTVcsVUFBVixFQUFzQjVHLE9BQU9oRCxJQUFQLENBQVk2SixJQUFaO0FBQ3RCO0FBQ0QsU0FBTzdHLE1BQVA7QUFDQSxFQWJEOztBQWVBO0FBQ0E7QUFDQW5DLEdBQUV1SSxVQUFGLEdBQWUsVUFBVWxCLEtBQVYsRUFBaUI7QUFDL0IsTUFBSUcsT0FBT0ksUUFBUTNHLFNBQVIsRUFBbUIsSUFBbkIsRUFBeUIsSUFBekIsRUFBK0IsQ0FBL0IsQ0FBWDtBQUNBLFNBQU9qQixFQUFFNEQsTUFBRixDQUFTeUQsS0FBVCxFQUFnQixVQUFVM0csS0FBVixFQUFpQjtBQUN2QyxVQUFPLENBQUNWLEVBQUVxRSxRQUFGLENBQVdtRCxJQUFYLEVBQWlCOUcsS0FBakIsQ0FBUjtBQUNBLEdBRk0sQ0FBUDtBQUdBLEVBTEQ7O0FBT0E7QUFDQTtBQUNBVixHQUFFaUosR0FBRixHQUFRLFlBQVk7QUFDbkIsU0FBT2pKLEVBQUVrSixLQUFGLENBQVFqSSxTQUFSLENBQVA7QUFDQSxFQUZEOztBQUlBO0FBQ0E7QUFDQWpCLEdBQUVrSixLQUFGLEdBQVUsVUFBVTdCLEtBQVYsRUFBaUI7QUFDMUIsTUFBSXhGLFNBQVN3RixTQUFTckgsRUFBRW9GLEdBQUYsQ0FBTWlDLEtBQU4sRUFBYSxRQUFiLEVBQXVCeEYsTUFBaEMsSUFBMEMsQ0FBdkQ7QUFDQSxNQUFJTSxTQUFTdEQsTUFBTWdELE1BQU4sQ0FBYjs7QUFFQSxPQUFLLElBQUloQixRQUFRLENBQWpCLEVBQW9CQSxRQUFRZ0IsTUFBNUIsRUFBb0NoQixPQUFwQyxFQUE2QztBQUM1Q3NCLFVBQU90QixLQUFQLElBQWdCYixFQUFFZ0YsS0FBRixDQUFRcUMsS0FBUixFQUFleEcsS0FBZixDQUFoQjtBQUNBO0FBQ0QsU0FBT3NCLE1BQVA7QUFDQSxFQVJEOztBQVVBO0FBQ0E7QUFDQTtBQUNBbkMsR0FBRW1KLE1BQUYsR0FBVyxVQUFVckYsSUFBVixFQUFnQlksTUFBaEIsRUFBd0I7QUFDbEMsTUFBSXZDLFNBQVMsRUFBYjtBQUNBLE9BQUssSUFBSUgsSUFBSSxDQUFSLEVBQVdILFNBQVNpQyxRQUFRQSxLQUFLakMsTUFBdEMsRUFBOENHLElBQUlILE1BQWxELEVBQTBERyxHQUExRCxFQUErRDtBQUM5RCxPQUFJMEMsTUFBSixFQUFZO0FBQ1h2QyxXQUFPMkIsS0FBSzlCLENBQUwsQ0FBUCxJQUFrQjBDLE9BQU8xQyxDQUFQLENBQWxCO0FBQ0EsSUFGRCxNQUVPO0FBQ05HLFdBQU8yQixLQUFLOUIsQ0FBTCxFQUFRLENBQVIsQ0FBUCxJQUFxQjhCLEtBQUs5QixDQUFMLEVBQVEsQ0FBUixDQUFyQjtBQUNBO0FBQ0Q7QUFDRCxTQUFPRyxNQUFQO0FBQ0EsRUFWRDs7QUFZQTtBQUNBO0FBQ0E7QUFDQTtBQUNBbkMsR0FBRTJFLE9BQUYsR0FBWSxVQUFVMEMsS0FBVixFQUFpQjJCLElBQWpCLEVBQXVCTixRQUF2QixFQUFpQztBQUM1QyxNQUFJMUcsSUFBSSxDQUFSO0FBQUEsTUFBV0gsU0FBU3dGLFNBQVNBLE1BQU14RixNQUFuQztBQUNBLE1BQUksT0FBTzZHLFFBQVAsSUFBbUIsUUFBdkIsRUFBaUM7QUFDaEMxRyxPQUFJMEcsV0FBVyxDQUFYLEdBQWVyRyxLQUFLK0MsR0FBTCxDQUFTLENBQVQsRUFBWXZELFNBQVM2RyxRQUFyQixDQUFmLEdBQWdEQSxRQUFwRDtBQUNBLEdBRkQsTUFFTyxJQUFJQSxZQUFZN0csTUFBaEIsRUFBd0I7QUFDOUJHLE9BQUloQyxFQUFFb0osV0FBRixDQUFjL0IsS0FBZCxFQUFxQjJCLElBQXJCLENBQUo7QUFDQSxVQUFPM0IsTUFBTXJGLENBQU4sTUFBYWdILElBQWIsR0FBb0JoSCxDQUFwQixHQUF3QixDQUFDLENBQWhDO0FBQ0E7QUFDRCxNQUFJZ0gsU0FBU0EsSUFBYixFQUFtQjtBQUNsQixVQUFPaEosRUFBRTBELFNBQUYsQ0FBWXRFLE1BQU11QixJQUFOLENBQVcwRyxLQUFYLEVBQWtCckYsQ0FBbEIsQ0FBWixFQUFrQ2hDLEVBQUVxSixLQUFwQyxDQUFQO0FBQ0E7QUFDRCxTQUFPckgsSUFBSUgsTUFBWCxFQUFtQkcsR0FBbkI7QUFBd0IsT0FBSXFGLE1BQU1yRixDQUFOLE1BQWFnSCxJQUFqQixFQUF1QixPQUFPaEgsQ0FBUDtBQUEvQyxHQUNBLE9BQU8sQ0FBQyxDQUFSO0FBQ0EsRUFiRDs7QUFlQWhDLEdBQUVzSixXQUFGLEdBQWdCLFVBQVVqQyxLQUFWLEVBQWlCMkIsSUFBakIsRUFBdUJPLElBQXZCLEVBQTZCO0FBQzVDLE1BQUlyQixNQUFNYixRQUFRQSxNQUFNeEYsTUFBZCxHQUF1QixDQUFqQztBQUNBLE1BQUksT0FBTzBILElBQVAsSUFBZSxRQUFuQixFQUE2QjtBQUM1QnJCLFNBQU1xQixPQUFPLENBQVAsR0FBV3JCLE1BQU1xQixJQUFOLEdBQWEsQ0FBeEIsR0FBNEJsSCxLQUFLa0QsR0FBTCxDQUFTMkMsR0FBVCxFQUFjcUIsT0FBTyxDQUFyQixDQUFsQztBQUNBO0FBQ0QsTUFBSVAsU0FBU0EsSUFBYixFQUFtQjtBQUNsQixVQUFPaEosRUFBRXdKLGFBQUYsQ0FBZ0JwSyxNQUFNdUIsSUFBTixDQUFXMEcsS0FBWCxFQUFrQixDQUFsQixFQUFxQmEsR0FBckIsQ0FBaEIsRUFBMkNsSSxFQUFFcUosS0FBN0MsQ0FBUDtBQUNBO0FBQ0QsU0FBTyxFQUFFbkIsR0FBRixJQUFTLENBQWhCO0FBQW1CLE9BQUliLE1BQU1hLEdBQU4sTUFBZWMsSUFBbkIsRUFBeUIsT0FBT2QsR0FBUDtBQUE1QyxHQUNBLE9BQU8sQ0FBQyxDQUFSO0FBQ0EsRUFWRDs7QUFZQTtBQUNBLFVBQVN1QixpQkFBVCxDQUEyQjFHLEdBQTNCLEVBQWdDO0FBQy9CLFNBQU8sVUFBVXNFLEtBQVYsRUFBaUI1RCxTQUFqQixFQUE0QmpELE9BQTVCLEVBQXFDO0FBQzNDaUQsZUFBWXZDLEdBQUd1QyxTQUFILEVBQWNqRCxPQUFkLENBQVo7QUFDQSxPQUFJcUIsU0FBU3dGLFNBQVMsSUFBVCxJQUFpQkEsTUFBTXhGLE1BQXBDO0FBQ0EsT0FBSWhCLFFBQVFrQyxNQUFNLENBQU4sR0FBVSxDQUFWLEdBQWNsQixTQUFTLENBQW5DO0FBQ0EsVUFBT2hCLFNBQVMsQ0FBVCxJQUFjQSxRQUFRZ0IsTUFBN0IsRUFBcUNoQixTQUFTa0MsR0FBOUMsRUFBbUQ7QUFDbEQsUUFBSVUsVUFBVTRELE1BQU14RyxLQUFOLENBQVYsRUFBd0JBLEtBQXhCLEVBQStCd0csS0FBL0IsQ0FBSixFQUEyQyxPQUFPeEcsS0FBUDtBQUMzQztBQUNELFVBQU8sQ0FBQyxDQUFSO0FBQ0EsR0FSRDtBQVNBOztBQUVEO0FBQ0FiLEdBQUUwRCxTQUFGLEdBQWMrRixrQkFBa0IsQ0FBbEIsQ0FBZDs7QUFFQXpKLEdBQUV3SixhQUFGLEdBQWtCQyxrQkFBa0IsQ0FBQyxDQUFuQixDQUFsQjs7QUFFQTtBQUNBO0FBQ0F6SixHQUFFb0osV0FBRixHQUFnQixVQUFVL0IsS0FBVixFQUFpQnBILEdBQWpCLEVBQXNCdUIsUUFBdEIsRUFBZ0NoQixPQUFoQyxFQUF5QztBQUN4RGdCLGFBQVdOLEdBQUdNLFFBQUgsRUFBYWhCLE9BQWIsRUFBc0IsQ0FBdEIsQ0FBWDtBQUNBLE1BQUlFLFFBQVFjLFNBQVN2QixHQUFULENBQVo7QUFDQSxNQUFJeUosTUFBTSxDQUFWO0FBQUEsTUFBYUMsT0FBT3RDLE1BQU14RixNQUExQjtBQUNBLFNBQU82SCxNQUFNQyxJQUFiLEVBQW1CO0FBQ2xCLE9BQUlDLE1BQU12SCxLQUFLd0gsS0FBTCxDQUFXLENBQUNILE1BQU1DLElBQVAsSUFBZSxDQUExQixDQUFWO0FBQ0EsT0FBSW5JLFNBQVM2RixNQUFNdUMsR0FBTixDQUFULElBQXVCbEosS0FBM0IsRUFBa0NnSixNQUFNRSxNQUFNLENBQVosQ0FBbEMsS0FBc0RELE9BQU9DLEdBQVA7QUFDdEQ7QUFDRCxTQUFPRixHQUFQO0FBQ0EsRUFURDs7QUFXQTtBQUNBO0FBQ0E7QUFDQTFKLEdBQUU4SixLQUFGLEdBQVUsVUFBVUMsS0FBVixFQUFpQkMsSUFBakIsRUFBdUJDLElBQXZCLEVBQTZCO0FBQ3RDLE1BQUloSixVQUFVWSxNQUFWLElBQW9CLENBQXhCLEVBQTJCO0FBQzFCbUksVUFBT0QsU0FBUyxDQUFoQjtBQUNBQSxXQUFRLENBQVI7QUFDQTtBQUNERSxTQUFPQSxRQUFRLENBQWY7O0FBRUEsTUFBSXBJLFNBQVNRLEtBQUsrQyxHQUFMLENBQVMvQyxLQUFLNkgsSUFBTCxDQUFVLENBQUNGLE9BQU9ELEtBQVIsSUFBaUJFLElBQTNCLENBQVQsRUFBMkMsQ0FBM0MsQ0FBYjtBQUNBLE1BQUlILFFBQVFqTCxNQUFNZ0QsTUFBTixDQUFaOztBQUVBLE9BQUssSUFBSXFHLE1BQU0sQ0FBZixFQUFrQkEsTUFBTXJHLE1BQXhCLEVBQWdDcUcsT0FBUTZCLFNBQVNFLElBQWpELEVBQXVEO0FBQ3RESCxTQUFNNUIsR0FBTixJQUFhNkIsS0FBYjtBQUNBOztBQUVELFNBQU9ELEtBQVA7QUFDQSxFQWZEOztBQWlCQTtBQUNBOztBQUVBO0FBQ0E7QUFDQSxLQUFJSyxlQUFlLFNBQWZBLFlBQWUsQ0FBVUMsVUFBVixFQUFzQkMsU0FBdEIsRUFBaUM3SixPQUFqQyxFQUEwQzhKLGNBQTFDLEVBQTBEeEYsSUFBMUQsRUFBZ0U7QUFDbEYsTUFBSSxFQUFFd0YsMEJBQTBCRCxTQUE1QixDQUFKLEVBQTRDLE9BQU9ELFdBQVdwSixLQUFYLENBQWlCUixPQUFqQixFQUEwQnNFLElBQTFCLENBQVA7QUFDNUMsTUFBSXlGLE9BQU9ySSxXQUFXa0ksV0FBV3RMLFNBQXRCLENBQVg7QUFDQSxNQUFJcUQsU0FBU2lJLFdBQVdwSixLQUFYLENBQWlCdUosSUFBakIsRUFBdUJ6RixJQUF2QixDQUFiO0FBQ0EsTUFBSTlFLEVBQUVxQixRQUFGLENBQVdjLE1BQVgsQ0FBSixFQUF3QixPQUFPQSxNQUFQO0FBQ3hCLFNBQU9vSSxJQUFQO0FBQ0EsRUFORDs7QUFRQTtBQUNBO0FBQ0E7QUFDQXZLLEdBQUVKLElBQUYsR0FBUyxVQUFVVyxJQUFWLEVBQWdCQyxPQUFoQixFQUF5QjtBQUNqQyxNQUFJYixjQUFjWSxLQUFLWCxJQUFMLEtBQWNELFVBQWhDLEVBQTRDLE9BQU9BLFdBQVdxQixLQUFYLENBQWlCVCxJQUFqQixFQUF1Qm5CLE1BQU11QixJQUFOLENBQVdNLFNBQVgsRUFBc0IsQ0FBdEIsQ0FBdkIsQ0FBUDtBQUM1QyxNQUFJLENBQUNqQixFQUFFb0IsVUFBRixDQUFhYixJQUFiLENBQUwsRUFBeUIsTUFBTSxJQUFJaUssU0FBSixDQUFjLG1DQUFkLENBQU47QUFDekIsTUFBSTFGLE9BQU8xRixNQUFNdUIsSUFBTixDQUFXTSxTQUFYLEVBQXNCLENBQXRCLENBQVg7QUFDQSxNQUFJd0osUUFBUSxTQUFSQSxLQUFRLEdBQVk7QUFDdkIsVUFBT04sYUFBYTVKLElBQWIsRUFBbUJrSyxLQUFuQixFQUEwQmpLLE9BQTFCLEVBQW1DLElBQW5DLEVBQXlDc0UsS0FBSzRGLE1BQUwsQ0FBWXRMLE1BQU11QixJQUFOLENBQVdNLFNBQVgsQ0FBWixDQUF6QyxDQUFQO0FBQ0EsR0FGRDtBQUdBLFNBQU93SixLQUFQO0FBQ0EsRUFSRDs7QUFVQTtBQUNBO0FBQ0E7QUFDQXpLLEdBQUUySyxPQUFGLEdBQVksVUFBVXBLLElBQVYsRUFBZ0I7QUFDM0IsTUFBSXFLLFlBQVl4TCxNQUFNdUIsSUFBTixDQUFXTSxTQUFYLEVBQXNCLENBQXRCLENBQWhCO0FBQ0EsTUFBSXdKLFFBQVEsU0FBUkEsS0FBUSxHQUFZO0FBQ3ZCLE9BQUlJLFdBQVcsQ0FBZjtBQUFBLE9BQWtCaEosU0FBUytJLFVBQVUvSSxNQUFyQztBQUNBLE9BQUlpRCxPQUFPakcsTUFBTWdELE1BQU4sQ0FBWDtBQUNBLFFBQUssSUFBSUcsSUFBSSxDQUFiLEVBQWdCQSxJQUFJSCxNQUFwQixFQUE0QkcsR0FBNUIsRUFBaUM7QUFDaEM4QyxTQUFLOUMsQ0FBTCxJQUFVNEksVUFBVTVJLENBQVYsTUFBaUJoQyxDQUFqQixHQUFxQmlCLFVBQVU0SixVQUFWLENBQXJCLEdBQTZDRCxVQUFVNUksQ0FBVixDQUF2RDtBQUNBO0FBQ0QsVUFBTzZJLFdBQVc1SixVQUFVWSxNQUE1QjtBQUFvQ2lELFNBQUszRixJQUFMLENBQVU4QixVQUFVNEosVUFBVixDQUFWO0FBQXBDLElBQ0EsT0FBT1YsYUFBYTVKLElBQWIsRUFBbUJrSyxLQUFuQixFQUEwQixJQUExQixFQUFnQyxJQUFoQyxFQUFzQzNGLElBQXRDLENBQVA7QUFDQSxHQVJEO0FBU0EsU0FBTzJGLEtBQVA7QUFDQSxFQVpEOztBQWNBO0FBQ0E7QUFDQTtBQUNBekssR0FBRThLLE9BQUYsR0FBWSxVQUFVN0ssR0FBVixFQUFlO0FBQzFCLE1BQUkrQixDQUFKO0FBQUEsTUFBT0gsU0FBU1osVUFBVVksTUFBMUI7QUFBQSxNQUFrQ0ksR0FBbEM7QUFDQSxNQUFJSixVQUFVLENBQWQsRUFBaUIsTUFBTSxJQUFJa0osS0FBSixDQUFVLHVDQUFWLENBQU47QUFDakIsT0FBSy9JLElBQUksQ0FBVCxFQUFZQSxJQUFJSCxNQUFoQixFQUF3QkcsR0FBeEIsRUFBNkI7QUFDNUJDLFNBQU1oQixVQUFVZSxDQUFWLENBQU47QUFDQS9CLE9BQUlnQyxHQUFKLElBQVdqQyxFQUFFSixJQUFGLENBQU9LLElBQUlnQyxHQUFKLENBQVAsRUFBaUJoQyxHQUFqQixDQUFYO0FBQ0E7QUFDRCxTQUFPQSxHQUFQO0FBQ0EsRUFSRDs7QUFVQTtBQUNBRCxHQUFFZ0wsT0FBRixHQUFZLFVBQVV6SyxJQUFWLEVBQWdCMEssTUFBaEIsRUFBd0I7QUFDbkMsTUFBSUQsVUFBVSxTQUFWQSxPQUFVLENBQVUvSSxHQUFWLEVBQWU7QUFDNUIsT0FBSWlKLFFBQVFGLFFBQVFFLEtBQXBCO0FBQ0EsT0FBSUMsVUFBVSxNQUFNRixTQUFTQSxPQUFPakssS0FBUCxDQUFhLElBQWIsRUFBbUJDLFNBQW5CLENBQVQsR0FBeUNnQixHQUEvQyxDQUFkO0FBQ0EsT0FBSSxDQUFDakMsRUFBRTBHLEdBQUYsQ0FBTXdFLEtBQU4sRUFBYUMsT0FBYixDQUFMLEVBQTRCRCxNQUFNQyxPQUFOLElBQWlCNUssS0FBS1MsS0FBTCxDQUFXLElBQVgsRUFBaUJDLFNBQWpCLENBQWpCO0FBQzVCLFVBQU9pSyxNQUFNQyxPQUFOLENBQVA7QUFDQSxHQUxEO0FBTUFILFVBQVFFLEtBQVIsR0FBZ0IsRUFBaEI7QUFDQSxTQUFPRixPQUFQO0FBQ0EsRUFURDs7QUFXQTtBQUNBO0FBQ0FoTCxHQUFFb0wsS0FBRixHQUFVLFVBQVU3SyxJQUFWLEVBQWdCOEssSUFBaEIsRUFBc0I7QUFDL0IsTUFBSXZHLE9BQU8xRixNQUFNdUIsSUFBTixDQUFXTSxTQUFYLEVBQXNCLENBQXRCLENBQVg7QUFDQSxTQUFPcUssV0FBVyxZQUFZO0FBQzdCLFVBQU8vSyxLQUFLUyxLQUFMLENBQVcsSUFBWCxFQUFpQjhELElBQWpCLENBQVA7QUFDQSxHQUZNLEVBRUp1RyxJQUZJLENBQVA7QUFHQSxFQUxEOztBQU9BO0FBQ0E7QUFDQXJMLEdBQUV1TCxLQUFGLEdBQVV2TCxFQUFFMkssT0FBRixDQUFVM0ssRUFBRW9MLEtBQVosRUFBbUJwTCxDQUFuQixFQUFzQixDQUF0QixDQUFWOztBQUVBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQUEsR0FBRXdMLFFBQUYsR0FBYSxVQUFVakwsSUFBVixFQUFnQjhLLElBQWhCLEVBQXNCSSxPQUF0QixFQUErQjtBQUMzQyxNQUFJakwsT0FBSixFQUFhc0UsSUFBYixFQUFtQjNDLE1BQW5CO0FBQ0EsTUFBSXVKLFVBQVUsSUFBZDtBQUNBLE1BQUlDLFdBQVcsQ0FBZjtBQUNBLE1BQUksQ0FBQ0YsT0FBTCxFQUFjQSxVQUFVLEVBQVY7QUFDZCxNQUFJRyxRQUFRLFNBQVJBLEtBQVEsR0FBWTtBQUN2QkQsY0FBV0YsUUFBUUksT0FBUixLQUFvQixLQUFwQixHQUE0QixDQUE1QixHQUFnQzdMLEVBQUU4TCxHQUFGLEVBQTNDO0FBQ0FKLGFBQVUsSUFBVjtBQUNBdkosWUFBUzVCLEtBQUtTLEtBQUwsQ0FBV1IsT0FBWCxFQUFvQnNFLElBQXBCLENBQVQ7QUFDQSxPQUFJLENBQUM0RyxPQUFMLEVBQWNsTCxVQUFVc0UsT0FBTyxJQUFqQjtBQUNkLEdBTEQ7QUFNQSxTQUFPLFlBQVk7QUFDbEIsT0FBSWdILE1BQU05TCxFQUFFOEwsR0FBRixFQUFWO0FBQ0EsT0FBSSxDQUFDSCxRQUFELElBQWFGLFFBQVFJLE9BQVIsS0FBb0IsS0FBckMsRUFBNENGLFdBQVdHLEdBQVg7QUFDNUMsT0FBSUMsWUFBWVYsUUFBUVMsTUFBTUgsUUFBZCxDQUFoQjtBQUNBbkwsYUFBVSxJQUFWO0FBQ0FzRSxVQUFPN0QsU0FBUDtBQUNBLE9BQUk4SyxhQUFhLENBQWIsSUFBa0JBLFlBQVlWLElBQWxDLEVBQXdDO0FBQ3ZDLFFBQUlLLE9BQUosRUFBYTtBQUNaTSxrQkFBYU4sT0FBYjtBQUNBQSxlQUFVLElBQVY7QUFDQTtBQUNEQyxlQUFXRyxHQUFYO0FBQ0EzSixhQUFTNUIsS0FBS1MsS0FBTCxDQUFXUixPQUFYLEVBQW9Cc0UsSUFBcEIsQ0FBVDtBQUNBLFFBQUksQ0FBQzRHLE9BQUwsRUFBY2xMLFVBQVVzRSxPQUFPLElBQWpCO0FBQ2QsSUFSRCxNQVFPLElBQUksQ0FBQzRHLE9BQUQsSUFBWUQsUUFBUVEsUUFBUixLQUFxQixLQUFyQyxFQUE0QztBQUNsRFAsY0FBVUosV0FBV00sS0FBWCxFQUFrQkcsU0FBbEIsQ0FBVjtBQUNBO0FBQ0QsVUFBTzVKLE1BQVA7QUFDQSxHQWxCRDtBQW1CQSxFQTlCRDs7QUFnQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQW5DLEdBQUVrTSxRQUFGLEdBQWEsVUFBVTNMLElBQVYsRUFBZ0I4SyxJQUFoQixFQUFzQmMsU0FBdEIsRUFBaUM7QUFDN0MsTUFBSVQsT0FBSixFQUFhNUcsSUFBYixFQUFtQnRFLE9BQW5CLEVBQTRCNEwsU0FBNUIsRUFBdUNqSyxNQUF2Qzs7QUFFQSxNQUFJeUosUUFBUSxTQUFSQSxLQUFRLEdBQVk7QUFDdkIsT0FBSXJFLE9BQU92SCxFQUFFOEwsR0FBRixLQUFVTSxTQUFyQjs7QUFFQSxPQUFJN0UsT0FBTzhELElBQVAsSUFBZTlELFFBQVEsQ0FBM0IsRUFBOEI7QUFDN0JtRSxjQUFVSixXQUFXTSxLQUFYLEVBQWtCUCxPQUFPOUQsSUFBekIsQ0FBVjtBQUNBLElBRkQsTUFFTztBQUNObUUsY0FBVSxJQUFWO0FBQ0EsUUFBSSxDQUFDUyxTQUFMLEVBQWdCO0FBQ2ZoSyxjQUFTNUIsS0FBS1MsS0FBTCxDQUFXUixPQUFYLEVBQW9Cc0UsSUFBcEIsQ0FBVDtBQUNBLFNBQUksQ0FBQzRHLE9BQUwsRUFBY2xMLFVBQVVzRSxPQUFPLElBQWpCO0FBQ2Q7QUFDRDtBQUNELEdBWkQ7O0FBY0EsU0FBTyxZQUFZO0FBQ2xCdEUsYUFBVSxJQUFWO0FBQ0FzRSxVQUFPN0QsU0FBUDtBQUNBbUwsZUFBWXBNLEVBQUU4TCxHQUFGLEVBQVo7QUFDQSxPQUFJTyxVQUFVRixhQUFhLENBQUNULE9BQTVCO0FBQ0EsT0FBSSxDQUFDQSxPQUFMLEVBQWNBLFVBQVVKLFdBQVdNLEtBQVgsRUFBa0JQLElBQWxCLENBQVY7QUFDZCxPQUFJZ0IsT0FBSixFQUFhO0FBQ1psSyxhQUFTNUIsS0FBS1MsS0FBTCxDQUFXUixPQUFYLEVBQW9Cc0UsSUFBcEIsQ0FBVDtBQUNBdEUsY0FBVXNFLE9BQU8sSUFBakI7QUFDQTs7QUFFRCxVQUFPM0MsTUFBUDtBQUNBLEdBWkQ7QUFhQSxFQTlCRDs7QUFnQ0E7QUFDQTtBQUNBO0FBQ0FuQyxHQUFFc00sSUFBRixHQUFTLFVBQVUvTCxJQUFWLEVBQWdCZ00sT0FBaEIsRUFBeUI7QUFDakMsU0FBT3ZNLEVBQUUySyxPQUFGLENBQVU0QixPQUFWLEVBQW1CaE0sSUFBbkIsQ0FBUDtBQUNBLEVBRkQ7O0FBSUE7QUFDQVAsR0FBRWdFLE1BQUYsR0FBVyxVQUFVUCxTQUFWLEVBQXFCO0FBQy9CLFNBQU8sWUFBWTtBQUNsQixVQUFPLENBQUNBLFVBQVV6QyxLQUFWLENBQWdCLElBQWhCLEVBQXNCQyxTQUF0QixDQUFSO0FBQ0EsR0FGRDtBQUdBLEVBSkQ7O0FBTUE7QUFDQTtBQUNBakIsR0FBRXdNLE9BQUYsR0FBWSxZQUFZO0FBQ3ZCLE1BQUkxSCxPQUFPN0QsU0FBWDtBQUNBLE1BQUk4SSxRQUFRakYsS0FBS2pELE1BQUwsR0FBYyxDQUExQjtBQUNBLFNBQU8sWUFBWTtBQUNsQixPQUFJRyxJQUFJK0gsS0FBUjtBQUNBLE9BQUk1SCxTQUFTMkMsS0FBS2lGLEtBQUwsRUFBWS9JLEtBQVosQ0FBa0IsSUFBbEIsRUFBd0JDLFNBQXhCLENBQWI7QUFDQSxVQUFPZSxHQUFQO0FBQVlHLGFBQVMyQyxLQUFLOUMsQ0FBTCxFQUFRckIsSUFBUixDQUFhLElBQWIsRUFBbUJ3QixNQUFuQixDQUFUO0FBQVosSUFDQSxPQUFPQSxNQUFQO0FBQ0EsR0FMRDtBQU1BLEVBVEQ7O0FBV0E7QUFDQW5DLEdBQUV5TSxLQUFGLEdBQVUsVUFBVUMsS0FBVixFQUFpQm5NLElBQWpCLEVBQXVCO0FBQ2hDLFNBQU8sWUFBWTtBQUNsQixPQUFJLEVBQUVtTSxLQUFGLEdBQVUsQ0FBZCxFQUFpQjtBQUNoQixXQUFPbk0sS0FBS1MsS0FBTCxDQUFXLElBQVgsRUFBaUJDLFNBQWpCLENBQVA7QUFDQTtBQUNELEdBSkQ7QUFLQSxFQU5EOztBQVFBO0FBQ0FqQixHQUFFMk0sTUFBRixHQUFXLFVBQVVELEtBQVYsRUFBaUJuTSxJQUFqQixFQUF1QjtBQUNqQyxNQUFJMEMsSUFBSjtBQUNBLFNBQU8sWUFBWTtBQUNsQixPQUFJLEVBQUV5SixLQUFGLEdBQVUsQ0FBZCxFQUFpQjtBQUNoQnpKLFdBQU8xQyxLQUFLUyxLQUFMLENBQVcsSUFBWCxFQUFpQkMsU0FBakIsQ0FBUDtBQUNBO0FBQ0QsT0FBSXlMLFNBQVMsQ0FBYixFQUFnQm5NLE9BQU8sSUFBUDtBQUNoQixVQUFPMEMsSUFBUDtBQUNBLEdBTkQ7QUFPQSxFQVREOztBQVdBO0FBQ0E7QUFDQWpELEdBQUU0TSxJQUFGLEdBQVM1TSxFQUFFMkssT0FBRixDQUFVM0ssRUFBRTJNLE1BQVosRUFBb0IsQ0FBcEIsQ0FBVDs7QUFFQTtBQUNBOztBQUVBO0FBQ0EsS0FBSUUsYUFBYSxDQUFDLEVBQUV4TixVQUFVLElBQVosR0FBbUJ5TixvQkFBbkIsQ0FBd0MsVUFBeEMsQ0FBbEI7QUFDQSxLQUFJQyxxQkFBcUIsQ0FBQyxTQUFELEVBQVksZUFBWixFQUE2QixVQUE3QixFQUN4QixzQkFEd0IsRUFDQSxnQkFEQSxFQUNrQixnQkFEbEIsQ0FBekI7O0FBR0EsVUFBU0MsbUJBQVQsQ0FBNkIvTSxHQUE3QixFQUFrQ1AsSUFBbEMsRUFBd0M7QUFDdkMsTUFBSXVOLGFBQWFGLG1CQUFtQmxMLE1BQXBDO0FBQ0EsTUFBSXFMLGNBQWNqTixJQUFJaU4sV0FBdEI7QUFDQSxNQUFJQyxRQUFTbk4sRUFBRW9CLFVBQUYsQ0FBYThMLFdBQWIsS0FBNkJBLFlBQVlwTyxTQUExQyxJQUF3REMsUUFBcEU7O0FBRUE7QUFDQSxNQUFJcU8sT0FBTyxhQUFYO0FBQ0EsTUFBSXBOLEVBQUUwRyxHQUFGLENBQU16RyxHQUFOLEVBQVdtTixJQUFYLEtBQW9CLENBQUNwTixFQUFFcUUsUUFBRixDQUFXM0UsSUFBWCxFQUFpQjBOLElBQWpCLENBQXpCLEVBQWlEMU4sS0FBS1AsSUFBTCxDQUFVaU8sSUFBVjs7QUFFakQsU0FBT0gsWUFBUCxFQUFxQjtBQUNwQkcsVUFBT0wsbUJBQW1CRSxVQUFuQixDQUFQO0FBQ0EsT0FBSUcsUUFBUW5OLEdBQVIsSUFBZUEsSUFBSW1OLElBQUosTUFBY0QsTUFBTUMsSUFBTixDQUE3QixJQUE0QyxDQUFDcE4sRUFBRXFFLFFBQUYsQ0FBVzNFLElBQVgsRUFBaUIwTixJQUFqQixDQUFqRCxFQUF5RTtBQUN4RTFOLFNBQUtQLElBQUwsQ0FBVWlPLElBQVY7QUFDQTtBQUNEO0FBQ0Q7O0FBRUQ7QUFDQTtBQUNBcE4sR0FBRU4sSUFBRixHQUFTLFVBQVVPLEdBQVYsRUFBZTtBQUN2QixNQUFJLENBQUNELEVBQUVxQixRQUFGLENBQVdwQixHQUFYLENBQUwsRUFBc0IsT0FBTyxFQUFQO0FBQ3RCLE1BQUlSLFVBQUosRUFBZ0IsT0FBT0EsV0FBV1EsR0FBWCxDQUFQO0FBQ2hCLE1BQUlQLE9BQU8sRUFBWDtBQUNBLE9BQUssSUFBSXVDLEdBQVQsSUFBZ0JoQyxHQUFoQjtBQUFxQixPQUFJRCxFQUFFMEcsR0FBRixDQUFNekcsR0FBTixFQUFXZ0MsR0FBWCxDQUFKLEVBQXFCdkMsS0FBS1AsSUFBTCxDQUFVOEMsR0FBVjtBQUExQyxHQUp1QixDQUt2QjtBQUNBLE1BQUk0SyxVQUFKLEVBQWdCRyxvQkFBb0IvTSxHQUFwQixFQUF5QlAsSUFBekI7QUFDaEIsU0FBT0EsSUFBUDtBQUNBLEVBUkQ7O0FBVUE7QUFDQU0sR0FBRXFOLE9BQUYsR0FBWSxVQUFVcE4sR0FBVixFQUFlO0FBQzFCLE1BQUksQ0FBQ0QsRUFBRXFCLFFBQUYsQ0FBV3BCLEdBQVgsQ0FBTCxFQUFzQixPQUFPLEVBQVA7QUFDdEIsTUFBSVAsT0FBTyxFQUFYO0FBQ0EsT0FBSyxJQUFJdUMsR0FBVCxJQUFnQmhDLEdBQWhCO0FBQXFCUCxRQUFLUCxJQUFMLENBQVU4QyxHQUFWO0FBQXJCLEdBSDBCLENBSTFCO0FBQ0EsTUFBSTRLLFVBQUosRUFBZ0JHLG9CQUFvQi9NLEdBQXBCLEVBQXlCUCxJQUF6QjtBQUNoQixTQUFPQSxJQUFQO0FBQ0EsRUFQRDs7QUFTQTtBQUNBTSxHQUFFMEUsTUFBRixHQUFXLFVBQVV6RSxHQUFWLEVBQWU7QUFDekIsTUFBSVAsT0FBT00sRUFBRU4sSUFBRixDQUFPTyxHQUFQLENBQVg7QUFDQSxNQUFJNEIsU0FBU25DLEtBQUttQyxNQUFsQjtBQUNBLE1BQUk2QyxTQUFTN0YsTUFBTWdELE1BQU4sQ0FBYjtBQUNBLE9BQUssSUFBSUcsSUFBSSxDQUFiLEVBQWdCQSxJQUFJSCxNQUFwQixFQUE0QkcsR0FBNUIsRUFBaUM7QUFDaEMwQyxVQUFPMUMsQ0FBUCxJQUFZL0IsSUFBSVAsS0FBS3NDLENBQUwsQ0FBSixDQUFaO0FBQ0E7QUFDRCxTQUFPMEMsTUFBUDtBQUNBLEVBUkQ7O0FBVUE7QUFDQTtBQUNBMUUsR0FBRXNOLFNBQUYsR0FBYyxVQUFVck4sR0FBVixFQUFldUIsUUFBZixFQUF5QmhCLE9BQXpCLEVBQWtDO0FBQy9DZ0IsYUFBV04sR0FBR00sUUFBSCxFQUFhaEIsT0FBYixDQUFYO0FBQ0EsTUFBSWQsT0FBT00sRUFBRU4sSUFBRixDQUFPTyxHQUFQLENBQVg7QUFBQSxNQUNDNEIsU0FBU25DLEtBQUttQyxNQURmO0FBQUEsTUFFQ2UsVUFBVSxFQUZYO0FBQUEsTUFHQ0MsVUFIRDtBQUlBLE9BQUssSUFBSWhDLFFBQVEsQ0FBakIsRUFBb0JBLFFBQVFnQixNQUE1QixFQUFvQ2hCLE9BQXBDLEVBQTZDO0FBQzVDZ0MsZ0JBQWFuRCxLQUFLbUIsS0FBTCxDQUFiO0FBQ0ErQixXQUFRQyxVQUFSLElBQXNCckIsU0FBU3ZCLElBQUk0QyxVQUFKLENBQVQsRUFBMEJBLFVBQTFCLEVBQXNDNUMsR0FBdEMsQ0FBdEI7QUFDQTtBQUNELFNBQU8yQyxPQUFQO0FBQ0EsRUFYRDs7QUFhQTtBQUNBNUMsR0FBRXVOLEtBQUYsR0FBVSxVQUFVdE4sR0FBVixFQUFlO0FBQ3hCLE1BQUlQLE9BQU9NLEVBQUVOLElBQUYsQ0FBT08sR0FBUCxDQUFYO0FBQ0EsTUFBSTRCLFNBQVNuQyxLQUFLbUMsTUFBbEI7QUFDQSxNQUFJMEwsUUFBUTFPLE1BQU1nRCxNQUFOLENBQVo7QUFDQSxPQUFLLElBQUlHLElBQUksQ0FBYixFQUFnQkEsSUFBSUgsTUFBcEIsRUFBNEJHLEdBQTVCLEVBQWlDO0FBQ2hDdUwsU0FBTXZMLENBQU4sSUFBVyxDQUFDdEMsS0FBS3NDLENBQUwsQ0FBRCxFQUFVL0IsSUFBSVAsS0FBS3NDLENBQUwsQ0FBSixDQUFWLENBQVg7QUFDQTtBQUNELFNBQU91TCxLQUFQO0FBQ0EsRUFSRDs7QUFVQTtBQUNBdk4sR0FBRXdOLE1BQUYsR0FBVyxVQUFVdk4sR0FBVixFQUFlO0FBQ3pCLE1BQUlrQyxTQUFTLEVBQWI7QUFDQSxNQUFJekMsT0FBT00sRUFBRU4sSUFBRixDQUFPTyxHQUFQLENBQVg7QUFDQSxPQUFLLElBQUkrQixJQUFJLENBQVIsRUFBV0gsU0FBU25DLEtBQUttQyxNQUE5QixFQUFzQ0csSUFBSUgsTUFBMUMsRUFBa0RHLEdBQWxELEVBQXVEO0FBQ3RERyxVQUFPbEMsSUFBSVAsS0FBS3NDLENBQUwsQ0FBSixDQUFQLElBQXVCdEMsS0FBS3NDLENBQUwsQ0FBdkI7QUFDQTtBQUNELFNBQU9HLE1BQVA7QUFDQSxFQVBEOztBQVNBO0FBQ0E7QUFDQW5DLEdBQUV5TixTQUFGLEdBQWN6TixFQUFFME4sT0FBRixHQUFZLFVBQVV6TixHQUFWLEVBQWU7QUFDeEMsTUFBSTBOLFFBQVEsRUFBWjtBQUNBLE9BQUssSUFBSTFMLEdBQVQsSUFBZ0JoQyxHQUFoQixFQUFxQjtBQUNwQixPQUFJRCxFQUFFb0IsVUFBRixDQUFhbkIsSUFBSWdDLEdBQUosQ0FBYixDQUFKLEVBQTRCMEwsTUFBTXhPLElBQU4sQ0FBVzhDLEdBQVg7QUFDNUI7QUFDRCxTQUFPMEwsTUFBTXpILElBQU4sRUFBUDtBQUNBLEVBTkQ7O0FBUUE7QUFDQWxHLEdBQUU0TixNQUFGLEdBQVdsTSxlQUFlMUIsRUFBRXFOLE9BQWpCLENBQVg7O0FBRUE7QUFDQTtBQUNBck4sR0FBRTZOLFNBQUYsR0FBYzdOLEVBQUU4TixNQUFGLEdBQVdwTSxlQUFlMUIsRUFBRU4sSUFBakIsQ0FBekI7O0FBRUE7QUFDQU0sR0FBRTJELE9BQUYsR0FBWSxVQUFVMUQsR0FBVixFQUFld0QsU0FBZixFQUEwQmpELE9BQTFCLEVBQW1DO0FBQzlDaUQsY0FBWXZDLEdBQUd1QyxTQUFILEVBQWNqRCxPQUFkLENBQVo7QUFDQSxNQUFJZCxPQUFPTSxFQUFFTixJQUFGLENBQU9PLEdBQVAsQ0FBWDtBQUFBLE1BQXdCZ0MsR0FBeEI7QUFDQSxPQUFLLElBQUlELElBQUksQ0FBUixFQUFXSCxTQUFTbkMsS0FBS21DLE1BQTlCLEVBQXNDRyxJQUFJSCxNQUExQyxFQUFrREcsR0FBbEQsRUFBdUQ7QUFDdERDLFNBQU12QyxLQUFLc0MsQ0FBTCxDQUFOO0FBQ0EsT0FBSXlCLFVBQVV4RCxJQUFJZ0MsR0FBSixDQUFWLEVBQW9CQSxHQUFwQixFQUF5QmhDLEdBQXpCLENBQUosRUFBbUMsT0FBT2dDLEdBQVA7QUFDbkM7QUFDRCxFQVBEOztBQVNBO0FBQ0FqQyxHQUFFK04sSUFBRixHQUFTLFVBQVU1RSxNQUFWLEVBQWtCNkUsU0FBbEIsRUFBNkJ4TixPQUE3QixFQUFzQztBQUM5QyxNQUFJMkIsU0FBUyxFQUFiO0FBQUEsTUFBaUJsQyxNQUFNa0osTUFBdkI7QUFBQSxNQUErQjNILFFBQS9CO0FBQUEsTUFBeUM5QixJQUF6QztBQUNBLE1BQUlPLE9BQU8sSUFBWCxFQUFpQixPQUFPa0MsTUFBUDtBQUNqQixNQUFJbkMsRUFBRW9CLFVBQUYsQ0FBYTRNLFNBQWIsQ0FBSixFQUE2QjtBQUM1QnRPLFVBQU9NLEVBQUVxTixPQUFGLENBQVVwTixHQUFWLENBQVA7QUFDQXVCLGNBQVdsQixXQUFXME4sU0FBWCxFQUFzQnhOLE9BQXRCLENBQVg7QUFDQSxHQUhELE1BR087QUFDTmQsVUFBT2tJLFFBQVEzRyxTQUFSLEVBQW1CLEtBQW5CLEVBQTBCLEtBQTFCLEVBQWlDLENBQWpDLENBQVA7QUFDQU8sY0FBVyxrQkFBVWQsS0FBVixFQUFpQnVCLEdBQWpCLEVBQXNCaEMsR0FBdEIsRUFBMkI7QUFBRSxXQUFPZ0MsT0FBT2hDLEdBQWQ7QUFBb0IsSUFBNUQ7QUFDQUEsU0FBTWpCLE9BQU9pQixHQUFQLENBQU47QUFDQTtBQUNELE9BQUssSUFBSStCLElBQUksQ0FBUixFQUFXSCxTQUFTbkMsS0FBS21DLE1BQTlCLEVBQXNDRyxJQUFJSCxNQUExQyxFQUFrREcsR0FBbEQsRUFBdUQ7QUFDdEQsT0FBSUMsTUFBTXZDLEtBQUtzQyxDQUFMLENBQVY7QUFDQSxPQUFJdEIsUUFBUVQsSUFBSWdDLEdBQUosQ0FBWjtBQUNBLE9BQUlULFNBQVNkLEtBQVQsRUFBZ0J1QixHQUFoQixFQUFxQmhDLEdBQXJCLENBQUosRUFBK0JrQyxPQUFPRixHQUFQLElBQWN2QixLQUFkO0FBQy9CO0FBQ0QsU0FBT3lCLE1BQVA7QUFDQSxFQWpCRDs7QUFtQkE7QUFDQW5DLEdBQUVpTyxJQUFGLEdBQVMsVUFBVWhPLEdBQVYsRUFBZXVCLFFBQWYsRUFBeUJoQixPQUF6QixFQUFrQztBQUMxQyxNQUFJUixFQUFFb0IsVUFBRixDQUFhSSxRQUFiLENBQUosRUFBNEI7QUFDM0JBLGNBQVd4QixFQUFFZ0UsTUFBRixDQUFTeEMsUUFBVCxDQUFYO0FBQ0EsR0FGRCxNQUVPO0FBQ04sT0FBSTlCLE9BQU9NLEVBQUUwQyxHQUFGLENBQU1rRixRQUFRM0csU0FBUixFQUFtQixLQUFuQixFQUEwQixLQUExQixFQUFpQyxDQUFqQyxDQUFOLEVBQTJDaU4sTUFBM0MsQ0FBWDtBQUNBMU0sY0FBVyxrQkFBVWQsS0FBVixFQUFpQnVCLEdBQWpCLEVBQXNCO0FBQ2hDLFdBQU8sQ0FBQ2pDLEVBQUVxRSxRQUFGLENBQVczRSxJQUFYLEVBQWlCdUMsR0FBakIsQ0FBUjtBQUNBLElBRkQ7QUFHQTtBQUNELFNBQU9qQyxFQUFFK04sSUFBRixDQUFPOU4sR0FBUCxFQUFZdUIsUUFBWixFQUFzQmhCLE9BQXRCLENBQVA7QUFDQSxFQVZEOztBQVlBO0FBQ0FSLEdBQUVtTyxRQUFGLEdBQWF6TSxlQUFlMUIsRUFBRXFOLE9BQWpCLEVBQTBCLElBQTFCLENBQWI7O0FBRUE7QUFDQTtBQUNBO0FBQ0FyTixHQUFFRixNQUFGLEdBQVcsVUFBVWhCLFNBQVYsRUFBcUJzUCxLQUFyQixFQUE0QjtBQUN0QyxNQUFJak0sU0FBU0QsV0FBV3BELFNBQVgsQ0FBYjtBQUNBLE1BQUlzUCxLQUFKLEVBQVdwTyxFQUFFNk4sU0FBRixDQUFZMUwsTUFBWixFQUFvQmlNLEtBQXBCO0FBQ1gsU0FBT2pNLE1BQVA7QUFDQSxFQUpEOztBQU1BO0FBQ0FuQyxHQUFFcU8sS0FBRixHQUFVLFVBQVVwTyxHQUFWLEVBQWU7QUFDeEIsTUFBSSxDQUFDRCxFQUFFcUIsUUFBRixDQUFXcEIsR0FBWCxDQUFMLEVBQXNCLE9BQU9BLEdBQVA7QUFDdEIsU0FBT0QsRUFBRVIsT0FBRixDQUFVUyxHQUFWLElBQWlCQSxJQUFJYixLQUFKLEVBQWpCLEdBQStCWSxFQUFFNE4sTUFBRixDQUFTLEVBQVQsRUFBYTNOLEdBQWIsQ0FBdEM7QUFDQSxFQUhEOztBQUtBO0FBQ0E7QUFDQTtBQUNBRCxHQUFFc08sR0FBRixHQUFRLFVBQVVyTyxHQUFWLEVBQWVzTyxXQUFmLEVBQTRCO0FBQ25DQSxjQUFZdE8sR0FBWjtBQUNBLFNBQU9BLEdBQVA7QUFDQSxFQUhEOztBQUtBO0FBQ0FELEdBQUV3TyxPQUFGLEdBQVksVUFBVXJGLE1BQVYsRUFBa0JqRSxLQUFsQixFQUF5QjtBQUNwQyxNQUFJeEYsT0FBT00sRUFBRU4sSUFBRixDQUFPd0YsS0FBUCxDQUFYO0FBQUEsTUFBMEJyRCxTQUFTbkMsS0FBS21DLE1BQXhDO0FBQ0EsTUFBSXNILFVBQVUsSUFBZCxFQUFvQixPQUFPLENBQUN0SCxNQUFSO0FBQ3BCLE1BQUk1QixNQUFNakIsT0FBT21LLE1BQVAsQ0FBVjtBQUNBLE9BQUssSUFBSW5ILElBQUksQ0FBYixFQUFnQkEsSUFBSUgsTUFBcEIsRUFBNEJHLEdBQTVCLEVBQWlDO0FBQ2hDLE9BQUlDLE1BQU12QyxLQUFLc0MsQ0FBTCxDQUFWO0FBQ0EsT0FBSWtELE1BQU1qRCxHQUFOLE1BQWVoQyxJQUFJZ0MsR0FBSixDQUFmLElBQTJCLEVBQUVBLE9BQU9oQyxHQUFULENBQS9CLEVBQThDLE9BQU8sS0FBUDtBQUM5QztBQUNELFNBQU8sSUFBUDtBQUNBLEVBVEQ7O0FBWUE7QUFDQSxLQUFJd08sS0FBSyxTQUFMQSxFQUFLLENBQVVwSSxDQUFWLEVBQWFDLENBQWIsRUFBZ0JvSSxNQUFoQixFQUF3QkMsTUFBeEIsRUFBZ0M7QUFDeEM7QUFDQTtBQUNBLE1BQUl0SSxNQUFNQyxDQUFWLEVBQWEsT0FBT0QsTUFBTSxDQUFOLElBQVcsSUFBSUEsQ0FBSixLQUFVLElBQUlDLENBQWhDO0FBQ2I7QUFDQSxNQUFJRCxLQUFLLElBQUwsSUFBYUMsS0FBSyxJQUF0QixFQUE0QixPQUFPRCxNQUFNQyxDQUFiO0FBQzVCO0FBQ0EsTUFBSUQsYUFBYXJHLENBQWpCLEVBQW9CcUcsSUFBSUEsRUFBRW5HLFFBQU47QUFDcEIsTUFBSW9HLGFBQWF0RyxDQUFqQixFQUFvQnNHLElBQUlBLEVBQUVwRyxRQUFOO0FBQ3BCO0FBQ0EsTUFBSTBPLFlBQVl2UCxTQUFTc0IsSUFBVCxDQUFjMEYsQ0FBZCxDQUFoQjtBQUNBLE1BQUl1SSxjQUFjdlAsU0FBU3NCLElBQVQsQ0FBYzJGLENBQWQsQ0FBbEIsRUFBb0MsT0FBTyxLQUFQO0FBQ3BDLFVBQVFzSSxTQUFSO0FBQ0M7QUFDQSxRQUFLLGlCQUFMO0FBQ0E7QUFDQSxRQUFLLGlCQUFMO0FBQ0M7QUFDQTtBQUNBLFdBQU8sS0FBS3ZJLENBQUwsS0FBVyxLQUFLQyxDQUF2QjtBQUNELFFBQUssaUJBQUw7QUFDQztBQUNBO0FBQ0EsUUFBSSxDQUFDRCxDQUFELEtBQU8sQ0FBQ0EsQ0FBWixFQUFlLE9BQU8sQ0FBQ0MsQ0FBRCxLQUFPLENBQUNBLENBQWY7QUFDZjtBQUNBLFdBQU8sQ0FBQ0QsQ0FBRCxLQUFPLENBQVAsR0FBVyxJQUFJLENBQUNBLENBQUwsS0FBVyxJQUFJQyxDQUExQixHQUE4QixDQUFDRCxDQUFELEtBQU8sQ0FBQ0MsQ0FBN0M7QUFDRCxRQUFLLGVBQUw7QUFDQSxRQUFLLGtCQUFMO0FBQ0M7QUFDQTtBQUNBO0FBQ0EsV0FBTyxDQUFDRCxDQUFELEtBQU8sQ0FBQ0MsQ0FBZjtBQW5CRjs7QUFzQkEsTUFBSXVJLFlBQVlELGNBQWMsZ0JBQTlCO0FBQ0EsTUFBSSxDQUFDQyxTQUFMLEVBQWdCO0FBQ2YsT0FBSSxRQUFPeEksQ0FBUCx5Q0FBT0EsQ0FBUCxNQUFZLFFBQVosSUFBd0IsUUFBT0MsQ0FBUCx5Q0FBT0EsQ0FBUCxNQUFZLFFBQXhDLEVBQWtELE9BQU8sS0FBUDs7QUFFbEQ7QUFDQTtBQUNBLE9BQUl3SSxRQUFRekksRUFBRTZHLFdBQWQ7QUFBQSxPQUEyQjZCLFFBQVF6SSxFQUFFNEcsV0FBckM7QUFDQSxPQUFJNEIsVUFBVUMsS0FBVixJQUFtQixFQUFFL08sRUFBRW9CLFVBQUYsQ0FBYTBOLEtBQWIsS0FBdUJBLGlCQUFpQkEsS0FBeEMsSUFDeEI5TyxFQUFFb0IsVUFBRixDQUFhMk4sS0FBYixDQUR3QixJQUNEQSxpQkFBaUJBLEtBRGxCLENBQW5CLElBRUMsaUJBQWlCMUksQ0FBakIsSUFBc0IsaUJBQWlCQyxDQUY1QyxFQUVnRDtBQUMvQyxXQUFPLEtBQVA7QUFDQTtBQUNEO0FBQ0Q7QUFDQTs7QUFFQTtBQUNBO0FBQ0FvSSxXQUFTQSxVQUFVLEVBQW5CO0FBQ0FDLFdBQVNBLFVBQVUsRUFBbkI7QUFDQSxNQUFJOU0sU0FBUzZNLE9BQU83TSxNQUFwQjtBQUNBLFNBQU9BLFFBQVAsRUFBaUI7QUFDaEI7QUFDQTtBQUNBLE9BQUk2TSxPQUFPN00sTUFBUCxNQUFtQndFLENBQXZCLEVBQTBCLE9BQU9zSSxPQUFPOU0sTUFBUCxNQUFtQnlFLENBQTFCO0FBQzFCOztBQUVEO0FBQ0FvSSxTQUFPdlAsSUFBUCxDQUFZa0gsQ0FBWjtBQUNBc0ksU0FBT3hQLElBQVAsQ0FBWW1ILENBQVo7O0FBRUE7QUFDQSxNQUFJdUksU0FBSixFQUFlO0FBQ2Q7QUFDQWhOLFlBQVN3RSxFQUFFeEUsTUFBWDtBQUNBLE9BQUlBLFdBQVd5RSxFQUFFekUsTUFBakIsRUFBeUIsT0FBTyxLQUFQO0FBQ3pCO0FBQ0EsVUFBT0EsUUFBUCxFQUFpQjtBQUNoQixRQUFJLENBQUM0TSxHQUFHcEksRUFBRXhFLE1BQUYsQ0FBSCxFQUFjeUUsRUFBRXpFLE1BQUYsQ0FBZCxFQUF5QjZNLE1BQXpCLEVBQWlDQyxNQUFqQyxDQUFMLEVBQStDLE9BQU8sS0FBUDtBQUMvQztBQUNELEdBUkQsTUFRTztBQUNOO0FBQ0EsT0FBSWpQLE9BQU9NLEVBQUVOLElBQUYsQ0FBTzJHLENBQVAsQ0FBWDtBQUFBLE9BQXNCcEUsR0FBdEI7QUFDQUosWUFBU25DLEtBQUttQyxNQUFkO0FBQ0E7QUFDQSxPQUFJN0IsRUFBRU4sSUFBRixDQUFPNEcsQ0FBUCxFQUFVekUsTUFBVixLQUFxQkEsTUFBekIsRUFBaUMsT0FBTyxLQUFQO0FBQ2pDLFVBQU9BLFFBQVAsRUFBaUI7QUFDaEI7QUFDQUksVUFBTXZDLEtBQUttQyxNQUFMLENBQU47QUFDQSxRQUFJLEVBQUU3QixFQUFFMEcsR0FBRixDQUFNSixDQUFOLEVBQVNyRSxHQUFULEtBQWlCd00sR0FBR3BJLEVBQUVwRSxHQUFGLENBQUgsRUFBV3FFLEVBQUVyRSxHQUFGLENBQVgsRUFBbUJ5TSxNQUFuQixFQUEyQkMsTUFBM0IsQ0FBbkIsQ0FBSixFQUE0RCxPQUFPLEtBQVA7QUFDNUQ7QUFDRDtBQUNEO0FBQ0FELFNBQU9NLEdBQVA7QUFDQUwsU0FBT0ssR0FBUDtBQUNBLFNBQU8sSUFBUDtBQUNBLEVBMUZEOztBQTRGQTtBQUNBaFAsR0FBRWlQLE9BQUYsR0FBWSxVQUFVNUksQ0FBVixFQUFhQyxDQUFiLEVBQWdCO0FBQzNCLFNBQU9tSSxHQUFHcEksQ0FBSCxFQUFNQyxDQUFOLENBQVA7QUFDQSxFQUZEOztBQUlBO0FBQ0E7QUFDQXRHLEdBQUVrUCxPQUFGLEdBQVksVUFBVWpQLEdBQVYsRUFBZTtBQUMxQixNQUFJQSxPQUFPLElBQVgsRUFBaUIsT0FBTyxJQUFQO0FBQ2pCLE1BQUlzQyxZQUFZdEMsR0FBWixNQUFxQkQsRUFBRVIsT0FBRixDQUFVUyxHQUFWLEtBQWtCRCxFQUFFbVAsUUFBRixDQUFXbFAsR0FBWCxDQUFsQixJQUFxQ0QsRUFBRW1JLFdBQUYsQ0FBY2xJLEdBQWQsQ0FBMUQsQ0FBSixFQUFtRixPQUFPQSxJQUFJNEIsTUFBSixLQUFlLENBQXRCO0FBQ25GLFNBQU83QixFQUFFTixJQUFGLENBQU9PLEdBQVAsRUFBWTRCLE1BQVosS0FBdUIsQ0FBOUI7QUFDQSxFQUpEOztBQU1BO0FBQ0E3QixHQUFFb1AsU0FBRixHQUFjLFVBQVVuUCxHQUFWLEVBQWU7QUFDNUIsU0FBTyxDQUFDLEVBQUVBLE9BQU9BLElBQUlvUCxRQUFKLEtBQWlCLENBQTFCLENBQVI7QUFDQSxFQUZEOztBQUlBO0FBQ0E7QUFDQXJQLEdBQUVSLE9BQUYsR0FBWUQsaUJBQWlCLFVBQVVVLEdBQVYsRUFBZTtBQUMzQyxTQUFPWixTQUFTc0IsSUFBVCxDQUFjVixHQUFkLE1BQXVCLGdCQUE5QjtBQUNBLEVBRkQ7O0FBSUE7QUFDQUQsR0FBRXFCLFFBQUYsR0FBYSxVQUFVcEIsR0FBVixFQUFlO0FBQzNCLE1BQUlxUCxjQUFjclAsR0FBZCx5Q0FBY0EsR0FBZCxDQUFKO0FBQ0EsU0FBT3FQLFNBQVMsVUFBVCxJQUF1QkEsU0FBUyxRQUFULElBQXFCLENBQUMsQ0FBQ3JQLEdBQXJEO0FBQ0EsRUFIRDs7QUFLQTtBQUNBRCxHQUFFd0MsSUFBRixDQUFPLENBQUMsV0FBRCxFQUFjLFVBQWQsRUFBMEIsUUFBMUIsRUFBb0MsUUFBcEMsRUFBOEMsTUFBOUMsRUFBc0QsUUFBdEQsRUFBZ0UsT0FBaEUsQ0FBUCxFQUFpRixVQUFVK00sSUFBVixFQUFnQjtBQUNoR3ZQLElBQUUsT0FBT3VQLElBQVQsSUFBaUIsVUFBVXRQLEdBQVYsRUFBZTtBQUMvQixVQUFPWixTQUFTc0IsSUFBVCxDQUFjVixHQUFkLE1BQXVCLGFBQWFzUCxJQUFiLEdBQW9CLEdBQWxEO0FBQ0EsR0FGRDtBQUdBLEVBSkQ7O0FBTUE7QUFDQTtBQUNBLEtBQUksQ0FBQ3ZQLEVBQUVtSSxXQUFGLENBQWNsSCxTQUFkLENBQUwsRUFBK0I7QUFDOUJqQixJQUFFbUksV0FBRixHQUFnQixVQUFVbEksR0FBVixFQUFlO0FBQzlCLFVBQU9ELEVBQUUwRyxHQUFGLENBQU16RyxHQUFOLEVBQVcsUUFBWCxDQUFQO0FBQ0EsR0FGRDtBQUdBOztBQUVEO0FBQ0E7QUFDQSxLQUFJLE9BQU8sR0FBUCxJQUFjLFVBQWQsSUFBNEIsUUFBT3VQLFNBQVAseUNBQU9BLFNBQVAsTUFBb0IsUUFBcEQsRUFBOEQ7QUFDN0R4UCxJQUFFb0IsVUFBRixHQUFlLFVBQVVuQixHQUFWLEVBQWU7QUFDN0IsVUFBTyxPQUFPQSxHQUFQLElBQWMsVUFBZCxJQUE0QixLQUFuQztBQUNBLEdBRkQ7QUFHQTs7QUFFRDtBQUNBRCxHQUFFeVAsUUFBRixHQUFhLFVBQVV4UCxHQUFWLEVBQWU7QUFDM0IsU0FBT3dQLFNBQVN4UCxHQUFULEtBQWlCLENBQUNvSixNQUFNcUcsV0FBV3pQLEdBQVgsQ0FBTixDQUF6QjtBQUNBLEVBRkQ7O0FBSUE7QUFDQUQsR0FBRXFKLEtBQUYsR0FBVSxVQUFVcEosR0FBVixFQUFlO0FBQ3hCLFNBQU9ELEVBQUUyUCxRQUFGLENBQVcxUCxHQUFYLEtBQW1CQSxRQUFRLENBQUNBLEdBQW5DO0FBQ0EsRUFGRDs7QUFJQTtBQUNBRCxHQUFFMkksU0FBRixHQUFjLFVBQVUxSSxHQUFWLEVBQWU7QUFDNUIsU0FBT0EsUUFBUSxJQUFSLElBQWdCQSxRQUFRLEtBQXhCLElBQWlDWixTQUFTc0IsSUFBVCxDQUFjVixHQUFkLE1BQXVCLGtCQUEvRDtBQUNBLEVBRkQ7O0FBSUE7QUFDQUQsR0FBRTRQLE1BQUYsR0FBVyxVQUFVM1AsR0FBVixFQUFlO0FBQ3pCLFNBQU9BLFFBQVEsSUFBZjtBQUNBLEVBRkQ7O0FBSUE7QUFDQUQsR0FBRTZQLFdBQUYsR0FBZ0IsVUFBVTVQLEdBQVYsRUFBZTtBQUM5QixTQUFPQSxRQUFRLEtBQUssQ0FBcEI7QUFDQSxFQUZEOztBQUlBO0FBQ0E7QUFDQUQsR0FBRTBHLEdBQUYsR0FBUSxVQUFVekcsR0FBVixFQUFlZ0MsR0FBZixFQUFvQjtBQUMzQixTQUFPaEMsT0FBTyxJQUFQLElBQWVYLGVBQWVxQixJQUFmLENBQW9CVixHQUFwQixFQUF5QmdDLEdBQXpCLENBQXRCO0FBQ0EsRUFGRDs7QUFJQTtBQUNBOztBQUVBO0FBQ0E7QUFDQWpDLEdBQUU4UCxVQUFGLEdBQWUsWUFBWTtBQUMxQkMsT0FBSy9QLENBQUwsR0FBU2dRLGtCQUFUO0FBQ0EsU0FBTyxJQUFQO0FBQ0EsRUFIRDs7QUFLQTtBQUNBaFEsR0FBRW1CLFFBQUYsR0FBYSxVQUFVVCxLQUFWLEVBQWlCO0FBQzdCLFNBQU9BLEtBQVA7QUFDQSxFQUZEOztBQUlBO0FBQ0FWLEdBQUVpUSxRQUFGLEdBQWEsVUFBVXZQLEtBQVYsRUFBaUI7QUFDN0IsU0FBTyxZQUFZO0FBQ2xCLFVBQU9BLEtBQVA7QUFDQSxHQUZEO0FBR0EsRUFKRDs7QUFNQVYsR0FBRWtRLElBQUYsR0FBUyxZQUFZLENBQUcsQ0FBeEI7O0FBRUFsUSxHQUFFdUIsUUFBRixHQUFhLFVBQVVVLEdBQVYsRUFBZTtBQUMzQixTQUFPLFVBQVVoQyxHQUFWLEVBQWU7QUFDckIsVUFBT0EsT0FBTyxJQUFQLEdBQWMsS0FBSyxDQUFuQixHQUF1QkEsSUFBSWdDLEdBQUosQ0FBOUI7QUFDQSxHQUZEO0FBR0EsRUFKRDs7QUFNQTtBQUNBakMsR0FBRW1RLFVBQUYsR0FBZSxVQUFVbFEsR0FBVixFQUFlO0FBQzdCLFNBQU9BLE9BQU8sSUFBUCxHQUFjLFlBQVksQ0FBRyxDQUE3QixHQUFnQyxVQUFVZ0MsR0FBVixFQUFlO0FBQ3JELFVBQU9oQyxJQUFJZ0MsR0FBSixDQUFQO0FBQ0EsR0FGRDtBQUdBLEVBSkQ7O0FBTUE7QUFDQTtBQUNBakMsR0FBRXNCLE9BQUYsR0FBWXRCLEVBQUVvUSxPQUFGLEdBQVksVUFBVWxMLEtBQVYsRUFBaUI7QUFDeENBLFVBQVFsRixFQUFFNk4sU0FBRixDQUFZLEVBQVosRUFBZ0IzSSxLQUFoQixDQUFSO0FBQ0EsU0FBTyxVQUFVakYsR0FBVixFQUFlO0FBQ3JCLFVBQU9ELEVBQUV3TyxPQUFGLENBQVV2TyxHQUFWLEVBQWVpRixLQUFmLENBQVA7QUFDQSxHQUZEO0FBR0EsRUFMRDs7QUFPQTtBQUNBbEYsR0FBRTBNLEtBQUYsR0FBVSxVQUFVNUcsQ0FBVixFQUFhdEUsUUFBYixFQUF1QmhCLE9BQXZCLEVBQWdDO0FBQ3pDLE1BQUk2UCxRQUFReFIsTUFBTXdELEtBQUsrQyxHQUFMLENBQVMsQ0FBVCxFQUFZVSxDQUFaLENBQU4sQ0FBWjtBQUNBdEUsYUFBV2xCLFdBQVdrQixRQUFYLEVBQXFCaEIsT0FBckIsRUFBOEIsQ0FBOUIsQ0FBWDtBQUNBLE9BQUssSUFBSXdCLElBQUksQ0FBYixFQUFnQkEsSUFBSThELENBQXBCLEVBQXVCOUQsR0FBdkI7QUFBNEJxTyxTQUFNck8sQ0FBTixJQUFXUixTQUFTUSxDQUFULENBQVg7QUFBNUIsR0FDQSxPQUFPcU8sS0FBUDtBQUNBLEVBTEQ7O0FBT0E7QUFDQXJRLEdBQUU0RixNQUFGLEdBQVcsVUFBVUwsR0FBVixFQUFlSCxHQUFmLEVBQW9CO0FBQzlCLE1BQUlBLE9BQU8sSUFBWCxFQUFpQjtBQUNoQkEsU0FBTUcsR0FBTjtBQUNBQSxTQUFNLENBQU47QUFDQTtBQUNELFNBQU9BLE1BQU1sRCxLQUFLd0gsS0FBTCxDQUFXeEgsS0FBS3VELE1BQUwsTUFBaUJSLE1BQU1HLEdBQU4sR0FBWSxDQUE3QixDQUFYLENBQWI7QUFDQSxFQU5EOztBQVFBO0FBQ0F2RixHQUFFOEwsR0FBRixHQUFRd0UsS0FBS3hFLEdBQUwsSUFBWSxZQUFZO0FBQy9CLFNBQU8sSUFBSXdFLElBQUosR0FBV0MsT0FBWCxFQUFQO0FBQ0EsRUFGRDs7QUFJQTtBQUNBLEtBQUlDLFlBQVk7QUFDZixPQUFLLE9BRFU7QUFFZixPQUFLLE1BRlU7QUFHZixPQUFLLE1BSFU7QUFJZixPQUFLLFFBSlU7QUFLZixPQUFLLFFBTFU7QUFNZixPQUFLO0FBTlUsRUFBaEI7QUFRQSxLQUFJQyxjQUFjelEsRUFBRXdOLE1BQUYsQ0FBU2dELFNBQVQsQ0FBbEI7O0FBRUE7QUFDQSxLQUFJRSxnQkFBZ0IsU0FBaEJBLGFBQWdCLENBQVVoTyxHQUFWLEVBQWU7QUFDbEMsTUFBSWlPLFVBQVUsU0FBVkEsT0FBVSxDQUFVQyxLQUFWLEVBQWlCO0FBQzlCLFVBQU9sTyxJQUFJa08sS0FBSixDQUFQO0FBQ0EsR0FGRDtBQUdBO0FBQ0EsTUFBSTlPLFNBQVMsUUFBUTlCLEVBQUVOLElBQUYsQ0FBT2dELEdBQVAsRUFBWW1PLElBQVosQ0FBaUIsR0FBakIsQ0FBUixHQUFnQyxHQUE3QztBQUNBLE1BQUlDLGFBQWFDLE9BQU9qUCxNQUFQLENBQWpCO0FBQ0EsTUFBSWtQLGdCQUFnQkQsT0FBT2pQLE1BQVAsRUFBZSxHQUFmLENBQXBCO0FBQ0EsU0FBTyxVQUFVbVAsTUFBVixFQUFrQjtBQUN4QkEsWUFBU0EsVUFBVSxJQUFWLEdBQWlCLEVBQWpCLEdBQXNCLEtBQUtBLE1BQXBDO0FBQ0EsVUFBT0gsV0FBV0ksSUFBWCxDQUFnQkQsTUFBaEIsSUFBMEJBLE9BQU9FLE9BQVAsQ0FBZUgsYUFBZixFQUE4QkwsT0FBOUIsQ0FBMUIsR0FBbUVNLE1BQTFFO0FBQ0EsR0FIRDtBQUlBLEVBWkQ7QUFhQWpSLEdBQUVvUixNQUFGLEdBQVdWLGNBQWNGLFNBQWQsQ0FBWDtBQUNBeFEsR0FBRXFSLFFBQUYsR0FBYVgsY0FBY0QsV0FBZCxDQUFiOztBQUVBO0FBQ0E7QUFDQXpRLEdBQUVtQyxNQUFGLEdBQVcsVUFBVWdILE1BQVYsRUFBa0I1SCxRQUFsQixFQUE0QitQLFFBQTVCLEVBQXNDO0FBQ2hELE1BQUk1USxRQUFReUksVUFBVSxJQUFWLEdBQWlCLEtBQUssQ0FBdEIsR0FBMEJBLE9BQU81SCxRQUFQLENBQXRDO0FBQ0EsTUFBSWIsVUFBVSxLQUFLLENBQW5CLEVBQXNCO0FBQ3JCQSxXQUFRNFEsUUFBUjtBQUNBO0FBQ0QsU0FBT3RSLEVBQUVvQixVQUFGLENBQWFWLEtBQWIsSUFBc0JBLE1BQU1DLElBQU4sQ0FBV3dJLE1BQVgsQ0FBdEIsR0FBMkN6SSxLQUFsRDtBQUNBLEVBTkQ7O0FBUUE7QUFDQTtBQUNBLEtBQUk2USxZQUFZLENBQWhCO0FBQ0F2UixHQUFFd1IsUUFBRixHQUFhLFVBQVVDLE1BQVYsRUFBa0I7QUFDOUIsTUFBSUMsS0FBSyxFQUFFSCxTQUFGLEdBQWMsRUFBdkI7QUFDQSxTQUFPRSxTQUFTQSxTQUFTQyxFQUFsQixHQUF1QkEsRUFBOUI7QUFDQSxFQUhEOztBQUtBO0FBQ0E7QUFDQTFSLEdBQUUyUixnQkFBRixHQUFxQjtBQUNwQkMsWUFBVSxpQkFEVTtBQUVwQkMsZUFBYSxrQkFGTztBQUdwQlQsVUFBUTtBQUhZLEVBQXJCOztBQU1BO0FBQ0E7QUFDQTtBQUNBLEtBQUlVLFVBQVUsTUFBZDs7QUFFQTtBQUNBO0FBQ0EsS0FBSUMsVUFBVTtBQUNiLE9BQUssR0FEUTtBQUViLFFBQU0sSUFGTztBQUdiLFFBQU0sR0FITztBQUliLFFBQU0sR0FKTztBQUtiLFlBQVUsT0FMRztBQU1iLFlBQVU7QUFORyxFQUFkOztBQVNBLEtBQUlwQixVQUFVLDJCQUFkOztBQUVBLEtBQUlxQixhQUFhLFNBQWJBLFVBQWEsQ0FBVXBCLEtBQVYsRUFBaUI7QUFDakMsU0FBTyxPQUFPbUIsUUFBUW5CLEtBQVIsQ0FBZDtBQUNBLEVBRkQ7O0FBSUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTVRLEdBQUVpUyxRQUFGLEdBQWEsVUFBVUMsSUFBVixFQUFnQkMsUUFBaEIsRUFBMEJDLFdBQTFCLEVBQXVDO0FBQ25ELE1BQUksQ0FBQ0QsUUFBRCxJQUFhQyxXQUFqQixFQUE4QkQsV0FBV0MsV0FBWDtBQUM5QkQsYUFBV25TLEVBQUVtTyxRQUFGLENBQVcsRUFBWCxFQUFlZ0UsUUFBZixFQUF5Qm5TLEVBQUUyUixnQkFBM0IsQ0FBWDs7QUFFQTtBQUNBLE1BQUlyUSxVQUFVeVAsT0FBTyxDQUNwQixDQUFDb0IsU0FBU2YsTUFBVCxJQUFtQlUsT0FBcEIsRUFBNkJoUSxNQURULEVBRXBCLENBQUNxUSxTQUFTTixXQUFULElBQXdCQyxPQUF6QixFQUFrQ2hRLE1BRmQsRUFHcEIsQ0FBQ3FRLFNBQVNQLFFBQVQsSUFBcUJFLE9BQXRCLEVBQStCaFEsTUFIWCxFQUluQitPLElBSm1CLENBSWQsR0FKYyxJQUlQLElBSkEsRUFJTSxHQUpOLENBQWQ7O0FBTUE7QUFDQSxNQUFJaFEsUUFBUSxDQUFaO0FBQ0EsTUFBSWlCLFNBQVMsUUFBYjtBQUNBb1EsT0FBS2YsT0FBTCxDQUFhN1AsT0FBYixFQUFzQixVQUFVc1AsS0FBVixFQUFpQlEsTUFBakIsRUFBeUJTLFdBQXpCLEVBQXNDRCxRQUF0QyxFQUFnRFMsTUFBaEQsRUFBd0Q7QUFDN0V2USxhQUFVb1EsS0FBSzlTLEtBQUwsQ0FBV3lCLEtBQVgsRUFBa0J3UixNQUFsQixFQUEwQmxCLE9BQTFCLENBQWtDUixPQUFsQyxFQUEyQ3FCLFVBQTNDLENBQVY7QUFDQW5SLFdBQVF3UixTQUFTekIsTUFBTS9PLE1BQXZCOztBQUVBLE9BQUl1UCxNQUFKLEVBQVk7QUFDWHRQLGNBQVUsZ0JBQWdCc1AsTUFBaEIsR0FBeUIsZ0NBQW5DO0FBQ0EsSUFGRCxNQUVPLElBQUlTLFdBQUosRUFBaUI7QUFDdkIvUCxjQUFVLGdCQUFnQitQLFdBQWhCLEdBQThCLHNCQUF4QztBQUNBLElBRk0sTUFFQSxJQUFJRCxRQUFKLEVBQWM7QUFDcEI5UCxjQUFVLFNBQVM4UCxRQUFULEdBQW9CLFVBQTlCO0FBQ0E7O0FBRUQ7QUFDQSxVQUFPaEIsS0FBUDtBQUNBLEdBZEQ7QUFlQTlPLFlBQVUsTUFBVjs7QUFFQTtBQUNBLE1BQUksQ0FBQ3FRLFNBQVNHLFFBQWQsRUFBd0J4USxTQUFTLHFCQUFxQkEsTUFBckIsR0FBOEIsS0FBdkM7O0FBRXhCQSxXQUFTLDZDQUNSLG1EQURRLEdBRVJBLE1BRlEsR0FFQyxlQUZWOztBQUlBLE1BQUk7QUFDSCxPQUFJeVEsU0FBUyxJQUFJclQsUUFBSixDQUFhaVQsU0FBU0csUUFBVCxJQUFxQixLQUFsQyxFQUF5QyxHQUF6QyxFQUE4Q3hRLE1BQTlDLENBQWI7QUFDQSxHQUZELENBRUUsT0FBTzBRLENBQVAsRUFBVTtBQUNYQSxLQUFFMVEsTUFBRixHQUFXQSxNQUFYO0FBQ0EsU0FBTTBRLENBQU47QUFDQTs7QUFFRCxNQUFJUCxXQUFXLFNBQVhBLFFBQVcsQ0FBVVEsSUFBVixFQUFnQjtBQUM5QixVQUFPRixPQUFPNVIsSUFBUCxDQUFZLElBQVosRUFBa0I4UixJQUFsQixFQUF3QnpTLENBQXhCLENBQVA7QUFDQSxHQUZEOztBQUlBO0FBQ0EsTUFBSTBTLFdBQVdQLFNBQVNHLFFBQVQsSUFBcUIsS0FBcEM7QUFDQUwsV0FBU25RLE1BQVQsR0FBa0IsY0FBYzRRLFFBQWQsR0FBeUIsTUFBekIsR0FBa0M1USxNQUFsQyxHQUEyQyxHQUE3RDs7QUFFQSxTQUFPbVEsUUFBUDtBQUNBLEVBdEREOztBQXdEQTtBQUNBalMsR0FBRTJTLEtBQUYsR0FBVSxVQUFVMVMsR0FBVixFQUFlO0FBQ3hCLE1BQUkyUyxXQUFXNVMsRUFBRUMsR0FBRixDQUFmO0FBQ0EyUyxXQUFTQyxNQUFULEdBQWtCLElBQWxCO0FBQ0EsU0FBT0QsUUFBUDtBQUNBLEVBSkQ7O0FBTUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBLEtBQUl6USxTQUFTLFNBQVRBLE1BQVMsQ0FBVXlRLFFBQVYsRUFBb0IzUyxHQUFwQixFQUF5QjtBQUNyQyxTQUFPMlMsU0FBU0MsTUFBVCxHQUFrQjdTLEVBQUVDLEdBQUYsRUFBTzBTLEtBQVAsRUFBbEIsR0FBbUMxUyxHQUExQztBQUNBLEVBRkQ7O0FBSUE7QUFDQUQsR0FBRThTLEtBQUYsR0FBVSxVQUFVN1MsR0FBVixFQUFlO0FBQ3hCRCxJQUFFd0MsSUFBRixDQUFPeEMsRUFBRXlOLFNBQUYsQ0FBWXhOLEdBQVosQ0FBUCxFQUF5QixVQUFVc1AsSUFBVixFQUFnQjtBQUN4QyxPQUFJaFAsT0FBT1AsRUFBRXVQLElBQUYsSUFBVXRQLElBQUlzUCxJQUFKLENBQXJCO0FBQ0F2UCxLQUFFbEIsU0FBRixDQUFZeVEsSUFBWixJQUFvQixZQUFZO0FBQy9CLFFBQUl6SyxPQUFPLENBQUMsS0FBSzVFLFFBQU4sQ0FBWDtBQUNBZixTQUFLNkIsS0FBTCxDQUFXOEQsSUFBWCxFQUFpQjdELFNBQWpCO0FBQ0EsV0FBT2tCLE9BQU8sSUFBUCxFQUFhNUIsS0FBS1MsS0FBTCxDQUFXaEIsQ0FBWCxFQUFjOEUsSUFBZCxDQUFiLENBQVA7QUFDQSxJQUpEO0FBS0EsR0FQRDtBQVFBLEVBVEQ7O0FBV0E7QUFDQTlFLEdBQUU4UyxLQUFGLENBQVE5UyxDQUFSOztBQUVBO0FBQ0FBLEdBQUV3QyxJQUFGLENBQU8sQ0FBQyxLQUFELEVBQVEsTUFBUixFQUFnQixTQUFoQixFQUEyQixPQUEzQixFQUFvQyxNQUFwQyxFQUE0QyxRQUE1QyxFQUFzRCxTQUF0RCxDQUFQLEVBQXlFLFVBQVUrTSxJQUFWLEVBQWdCO0FBQ3hGLE1BQUkxSyxTQUFTakcsV0FBVzJRLElBQVgsQ0FBYjtBQUNBdlAsSUFBRWxCLFNBQUYsQ0FBWXlRLElBQVosSUFBb0IsWUFBWTtBQUMvQixPQUFJdFAsTUFBTSxLQUFLQyxRQUFmO0FBQ0EyRSxVQUFPN0QsS0FBUCxDQUFhZixHQUFiLEVBQWtCZ0IsU0FBbEI7QUFDQSxPQUFJLENBQUNzTyxTQUFTLE9BQVQsSUFBb0JBLFNBQVMsUUFBOUIsS0FBMkN0UCxJQUFJNEIsTUFBSixLQUFlLENBQTlELEVBQWlFLE9BQU81QixJQUFJLENBQUosQ0FBUDtBQUNqRSxVQUFPa0MsT0FBTyxJQUFQLEVBQWFsQyxHQUFiLENBQVA7QUFDQSxHQUxEO0FBTUEsRUFSRDs7QUFVQTtBQUNBRCxHQUFFd0MsSUFBRixDQUFPLENBQUMsUUFBRCxFQUFXLE1BQVgsRUFBbUIsT0FBbkIsQ0FBUCxFQUFvQyxVQUFVK00sSUFBVixFQUFnQjtBQUNuRCxNQUFJMUssU0FBU2pHLFdBQVcyUSxJQUFYLENBQWI7QUFDQXZQLElBQUVsQixTQUFGLENBQVl5USxJQUFaLElBQW9CLFlBQVk7QUFDL0IsVUFBT3BOLE9BQU8sSUFBUCxFQUFhMEMsT0FBTzdELEtBQVAsQ0FBYSxLQUFLZCxRQUFsQixFQUE0QmUsU0FBNUIsQ0FBYixDQUFQO0FBQ0EsR0FGRDtBQUdBLEVBTEQ7O0FBT0E7QUFDQWpCLEdBQUVsQixTQUFGLENBQVk0QixLQUFaLEdBQW9CLFlBQVk7QUFDL0IsU0FBTyxLQUFLUixRQUFaO0FBQ0EsRUFGRDs7QUFJQTtBQUNBO0FBQ0FGLEdBQUVsQixTQUFGLENBQVlpVSxPQUFaLEdBQXNCL1MsRUFBRWxCLFNBQUYsQ0FBWWtVLE1BQVosR0FBcUJoVCxFQUFFbEIsU0FBRixDQUFZNEIsS0FBdkQ7O0FBRUFWLEdBQUVsQixTQUFGLENBQVlPLFFBQVosR0FBdUIsWUFBWTtBQUNsQyxTQUFPLEtBQUssS0FBS2EsUUFBakI7QUFDQSxFQUZEOztBQUlBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLENBbmdEQSxFQW1nRENTLElBbmdERCxXQUFEIiwiZmlsZSI6InVuZGVyc2NvcmUuanMiLCJzb3VyY2VzQ29udGVudCI6WyIvLyAgICAgVW5kZXJzY29yZS5qcyAxLjguMlxuLy8gICAgIGh0dHA6Ly91bmRlcnNjb3JlanMub3JnXG4vLyAgICAgKGMpIDIwMDktMjAxNSBKZXJlbXkgQXNoa2VuYXMsIERvY3VtZW50Q2xvdWQgYW5kIEludmVzdGlnYXRpdmUgUmVwb3J0ZXJzICYgRWRpdG9yc1xuLy8gICAgIFVuZGVyc2NvcmUgbWF5IGJlIGZyZWVseSBkaXN0cmlidXRlZCB1bmRlciB0aGUgTUlUIGxpY2Vuc2UuXG5cbihmdW5jdGlvbiAoKSB7XG5cblx0Ly8gQmFzZWxpbmUgc2V0dXBcblx0Ly8gLS0tLS0tLS0tLS0tLS1cblxuXHQvLyBFc3RhYmxpc2ggdGhlIHJvb3Qgb2JqZWN0LCBgd2luZG93YCBpbiB0aGUgYnJvd3Nlciwgb3IgYGV4cG9ydHNgIG9uIHRoZSBzZXJ2ZXIuXG5cdC8vICAgdmFyIHJvb3QgPSB0aGlzO1xuXG5cdC8vICAgLy8gU2F2ZSB0aGUgcHJldmlvdXMgdmFsdWUgb2YgdGhlIGBfYCB2YXJpYWJsZS5cblx0Ly8gICB2YXIgcHJldmlvdXNVbmRlcnNjb3JlID0gcm9vdC5fO1xuXG5cdC8vIFNhdmUgYnl0ZXMgaW4gdGhlIG1pbmlmaWVkIChidXQgbm90IGd6aXBwZWQpIHZlcnNpb246XG5cdHZhciBBcnJheVByb3RvID0gQXJyYXkucHJvdG90eXBlLCBPYmpQcm90byA9IE9iamVjdC5wcm90b3R5cGUsIEZ1bmNQcm90byA9IEZ1bmN0aW9uLnByb3RvdHlwZTtcblxuXHQvLyBDcmVhdGUgcXVpY2sgcmVmZXJlbmNlIHZhcmlhYmxlcyBmb3Igc3BlZWQgYWNjZXNzIHRvIGNvcmUgcHJvdG90eXBlcy5cblx0dmFyXG5cdFx0cHVzaCA9IEFycmF5UHJvdG8ucHVzaCxcblx0XHRzbGljZSA9IEFycmF5UHJvdG8uc2xpY2UsXG5cdFx0dG9TdHJpbmcgPSBPYmpQcm90by50b1N0cmluZyxcblx0XHRoYXNPd25Qcm9wZXJ0eSA9IE9ialByb3RvLmhhc093blByb3BlcnR5O1xuXG5cdC8vIEFsbCAqKkVDTUFTY3JpcHQgNSoqIG5hdGl2ZSBmdW5jdGlvbiBpbXBsZW1lbnRhdGlvbnMgdGhhdCB3ZSBob3BlIHRvIHVzZVxuXHQvLyBhcmUgZGVjbGFyZWQgaGVyZS5cblx0dmFyXG5cdFx0bmF0aXZlSXNBcnJheSA9IEFycmF5LmlzQXJyYXksXG5cdFx0bmF0aXZlS2V5cyA9IE9iamVjdC5rZXlzLFxuXHRcdG5hdGl2ZUJpbmQgPSBGdW5jUHJvdG8uYmluZCxcblx0XHRuYXRpdmVDcmVhdGUgPSBPYmplY3QuY3JlYXRlO1xuXG5cdC8vIE5ha2VkIGZ1bmN0aW9uIHJlZmVyZW5jZSBmb3Igc3Vycm9nYXRlLXByb3RvdHlwZS1zd2FwcGluZy5cblx0dmFyIEN0b3IgPSBmdW5jdGlvbiAoKSB7IH07XG5cblx0Ly8gQ3JlYXRlIGEgc2FmZSByZWZlcmVuY2UgdG8gdGhlIFVuZGVyc2NvcmUgb2JqZWN0IGZvciB1c2UgYmVsb3cuXG5cdHZhciBfID0gZnVuY3Rpb24gKG9iaikge1xuXHRcdGlmIChvYmogaW5zdGFuY2VvZiBfKSByZXR1cm4gb2JqO1xuXHRcdGlmICghKHRoaXMgaW5zdGFuY2VvZiBfKSkgcmV0dXJuIG5ldyBfKG9iaik7XG5cdFx0dGhpcy5fd3JhcHBlZCA9IG9iajtcblx0fTtcblxuXHQvLyBFeHBvcnQgdGhlIFVuZGVyc2NvcmUgb2JqZWN0IGZvciAqKk5vZGUuanMqKiwgd2l0aFxuXHQvLyBiYWNrd2FyZHMtY29tcGF0aWJpbGl0eSBmb3IgdGhlIG9sZCBgcmVxdWlyZSgpYCBBUEkuIElmIHdlJ3JlIGluXG5cdC8vIHRoZSBicm93c2VyLCBhZGQgYF9gIGFzIGEgZ2xvYmFsIG9iamVjdC5cblx0Ly8gICBpZiAodHlwZW9mIGV4cG9ydHMgIT09ICd1bmRlZmluZWQnKSB7XG5cdC8vICAgICBpZiAodHlwZW9mIG1vZHVsZSAhPT0gJ3VuZGVmaW5lZCcgJiYgbW9kdWxlLmV4cG9ydHMpIHtcblx0Ly8gICAgICAgZXhwb3J0cyA9IG1vZHVsZS5leHBvcnRzID0gXztcblx0Ly8gICAgIH1cblx0Ly8gICAgIGV4cG9ydHMuXyA9IF87XG5cdC8vICAgfSBlbHNlIHtcblx0Ly8gICAgIHJvb3QuXyA9IF87XG5cdC8vICAgfVxuXHRtb2R1bGUuZXhwb3J0cyA9IF87XG5cdC8vIEN1cnJlbnQgdmVyc2lvbi5cblx0Xy5WRVJTSU9OID0gJzEuOC4yJztcblxuXHQvLyBJbnRlcm5hbCBmdW5jdGlvbiB0aGF0IHJldHVybnMgYW4gZWZmaWNpZW50IChmb3IgY3VycmVudCBlbmdpbmVzKSB2ZXJzaW9uXG5cdC8vIG9mIHRoZSBwYXNzZWQtaW4gY2FsbGJhY2ssIHRvIGJlIHJlcGVhdGVkbHkgYXBwbGllZCBpbiBvdGhlciBVbmRlcnNjb3JlXG5cdC8vIGZ1bmN0aW9ucy5cblx0dmFyIG9wdGltaXplQ2IgPSBmdW5jdGlvbiAoZnVuYywgY29udGV4dCwgYXJnQ291bnQpIHtcblx0XHRpZiAoY29udGV4dCA9PT0gdm9pZCAwKSByZXR1cm4gZnVuYztcblx0XHRzd2l0Y2ggKGFyZ0NvdW50ID09IG51bGwgPyAzIDogYXJnQ291bnQpIHtcblx0XHRcdGNhc2UgMTogcmV0dXJuIGZ1bmN0aW9uICh2YWx1ZSkge1xuXHRcdFx0XHRyZXR1cm4gZnVuYy5jYWxsKGNvbnRleHQsIHZhbHVlKTtcblx0XHRcdH07XG5cdFx0XHRjYXNlIDI6IHJldHVybiBmdW5jdGlvbiAodmFsdWUsIG90aGVyKSB7XG5cdFx0XHRcdHJldHVybiBmdW5jLmNhbGwoY29udGV4dCwgdmFsdWUsIG90aGVyKTtcblx0XHRcdH07XG5cdFx0XHRjYXNlIDM6IHJldHVybiBmdW5jdGlvbiAodmFsdWUsIGluZGV4LCBjb2xsZWN0aW9uKSB7XG5cdFx0XHRcdHJldHVybiBmdW5jLmNhbGwoY29udGV4dCwgdmFsdWUsIGluZGV4LCBjb2xsZWN0aW9uKTtcblx0XHRcdH07XG5cdFx0XHRjYXNlIDQ6IHJldHVybiBmdW5jdGlvbiAoYWNjdW11bGF0b3IsIHZhbHVlLCBpbmRleCwgY29sbGVjdGlvbikge1xuXHRcdFx0XHRyZXR1cm4gZnVuYy5jYWxsKGNvbnRleHQsIGFjY3VtdWxhdG9yLCB2YWx1ZSwgaW5kZXgsIGNvbGxlY3Rpb24pO1xuXHRcdFx0fTtcblx0XHR9XG5cdFx0cmV0dXJuIGZ1bmN0aW9uICgpIHtcblx0XHRcdHJldHVybiBmdW5jLmFwcGx5KGNvbnRleHQsIGFyZ3VtZW50cyk7XG5cdFx0fTtcblx0fTtcblxuXHQvLyBBIG1vc3RseS1pbnRlcm5hbCBmdW5jdGlvbiB0byBnZW5lcmF0ZSBjYWxsYmFja3MgdGhhdCBjYW4gYmUgYXBwbGllZFxuXHQvLyB0byBlYWNoIGVsZW1lbnQgaW4gYSBjb2xsZWN0aW9uLCByZXR1cm5pbmcgdGhlIGRlc2lyZWQgcmVzdWx0IOmIpe+/vSBlaXRoZXJcblx0Ly8gaWRlbnRpdHksIGFuIGFyYml0cmFyeSBjYWxsYmFjaywgYSBwcm9wZXJ0eSBtYXRjaGVyLCBvciBhIHByb3BlcnR5IGFjY2Vzc29yLlxuXHR2YXIgY2IgPSBmdW5jdGlvbiAodmFsdWUsIGNvbnRleHQsIGFyZ0NvdW50KSB7XG5cdFx0aWYgKHZhbHVlID09IG51bGwpIHJldHVybiBfLmlkZW50aXR5O1xuXHRcdGlmIChfLmlzRnVuY3Rpb24odmFsdWUpKSByZXR1cm4gb3B0aW1pemVDYih2YWx1ZSwgY29udGV4dCwgYXJnQ291bnQpO1xuXHRcdGlmIChfLmlzT2JqZWN0KHZhbHVlKSkgcmV0dXJuIF8ubWF0Y2hlcih2YWx1ZSk7XG5cdFx0cmV0dXJuIF8ucHJvcGVydHkodmFsdWUpO1xuXHR9O1xuXHRfLml0ZXJhdGVlID0gZnVuY3Rpb24gKHZhbHVlLCBjb250ZXh0KSB7XG5cdFx0cmV0dXJuIGNiKHZhbHVlLCBjb250ZXh0LCBJbmZpbml0eSk7XG5cdH07XG5cblx0Ly8gQW4gaW50ZXJuYWwgZnVuY3Rpb24gZm9yIGNyZWF0aW5nIGFzc2lnbmVyIGZ1bmN0aW9ucy5cblx0dmFyIGNyZWF0ZUFzc2lnbmVyID0gZnVuY3Rpb24gKGtleXNGdW5jLCB1bmRlZmluZWRPbmx5KSB7XG5cdFx0cmV0dXJuIGZ1bmN0aW9uIChvYmopIHtcblx0XHRcdHZhciBsZW5ndGggPSBhcmd1bWVudHMubGVuZ3RoO1xuXHRcdFx0aWYgKGxlbmd0aCA8IDIgfHwgb2JqID09IG51bGwpIHJldHVybiBvYmo7XG5cdFx0XHRmb3IgKHZhciBpbmRleCA9IDE7IGluZGV4IDwgbGVuZ3RoOyBpbmRleCsrKSB7XG5cdFx0XHRcdHZhciBzb3VyY2UgPSBhcmd1bWVudHNbaW5kZXhdLFxuXHRcdFx0XHRcdGtleXMgPSBrZXlzRnVuYyhzb3VyY2UpLFxuXHRcdFx0XHRcdGwgPSBrZXlzLmxlbmd0aDtcblx0XHRcdFx0Zm9yICh2YXIgaSA9IDA7IGkgPCBsOyBpKyspIHtcblx0XHRcdFx0XHR2YXIga2V5ID0ga2V5c1tpXTtcblx0XHRcdFx0XHRpZiAoIXVuZGVmaW5lZE9ubHkgfHwgb2JqW2tleV0gPT09IHZvaWQgMCkgb2JqW2tleV0gPSBzb3VyY2Vba2V5XTtcblx0XHRcdFx0fVxuXHRcdFx0fVxuXHRcdFx0cmV0dXJuIG9iajtcblx0XHR9O1xuXHR9O1xuXG5cdC8vIEFuIGludGVybmFsIGZ1bmN0aW9uIGZvciBjcmVhdGluZyBhIG5ldyBvYmplY3QgdGhhdCBpbmhlcml0cyBmcm9tIGFub3RoZXIuXG5cdHZhciBiYXNlQ3JlYXRlID0gZnVuY3Rpb24gKHByb3RvdHlwZSkge1xuXHRcdGlmICghXy5pc09iamVjdChwcm90b3R5cGUpKSByZXR1cm4ge307XG5cdFx0aWYgKG5hdGl2ZUNyZWF0ZSkgcmV0dXJuIG5hdGl2ZUNyZWF0ZShwcm90b3R5cGUpO1xuXHRcdEN0b3IucHJvdG90eXBlID0gcHJvdG90eXBlO1xuXHRcdHZhciByZXN1bHQgPSBuZXcgQ3Rvcjtcblx0XHRDdG9yLnByb3RvdHlwZSA9IG51bGw7XG5cdFx0cmV0dXJuIHJlc3VsdDtcblx0fTtcblxuXHQvLyBIZWxwZXIgZm9yIGNvbGxlY3Rpb24gbWV0aG9kcyB0byBkZXRlcm1pbmUgd2hldGhlciBhIGNvbGxlY3Rpb25cblx0Ly8gc2hvdWxkIGJlIGl0ZXJhdGVkIGFzIGFuIGFycmF5IG9yIGFzIGFuIG9iamVjdFxuXHQvLyBSZWxhdGVkOiBodHRwOi8vcGVvcGxlLm1vemlsbGEub3JnL35qb3JlbmRvcmZmL2VzNi1kcmFmdC5odG1sI3NlYy10b2xlbmd0aFxuXHR2YXIgTUFYX0FSUkFZX0lOREVYID0gTWF0aC5wb3coMiwgNTMpIC0gMTtcblx0dmFyIGlzQXJyYXlMaWtlID0gZnVuY3Rpb24gKGNvbGxlY3Rpb24pIHtcblx0XHR2YXIgbGVuZ3RoID0gY29sbGVjdGlvbiAhPSBudWxsICYmIGNvbGxlY3Rpb24ubGVuZ3RoO1xuXHRcdHJldHVybiB0eXBlb2YgbGVuZ3RoID09ICdudW1iZXInICYmIGxlbmd0aCA+PSAwICYmIGxlbmd0aCA8PSBNQVhfQVJSQVlfSU5ERVg7XG5cdH07XG5cblx0Ly8gQ29sbGVjdGlvbiBGdW5jdGlvbnNcblx0Ly8gLS0tLS0tLS0tLS0tLS0tLS0tLS1cblxuXHQvLyBUaGUgY29ybmVyc3RvbmUsIGFuIGBlYWNoYCBpbXBsZW1lbnRhdGlvbiwgYWthIGBmb3JFYWNoYC5cblx0Ly8gSGFuZGxlcyByYXcgb2JqZWN0cyBpbiBhZGRpdGlvbiB0byBhcnJheS1saWtlcy4gVHJlYXRzIGFsbFxuXHQvLyBzcGFyc2UgYXJyYXktbGlrZXMgYXMgaWYgdGhleSB3ZXJlIGRlbnNlLlxuXHRfLmVhY2ggPSBfLmZvckVhY2ggPSBmdW5jdGlvbiAob2JqLCBpdGVyYXRlZSwgY29udGV4dCkge1xuXHRcdGl0ZXJhdGVlID0gb3B0aW1pemVDYihpdGVyYXRlZSwgY29udGV4dCk7XG5cdFx0dmFyIGksIGxlbmd0aDtcblx0XHRpZiAoaXNBcnJheUxpa2Uob2JqKSkge1xuXHRcdFx0Zm9yIChpID0gMCwgbGVuZ3RoID0gb2JqLmxlbmd0aDsgaSA8IGxlbmd0aDsgaSsrKSB7XG5cdFx0XHRcdGl0ZXJhdGVlKG9ialtpXSwgaSwgb2JqKTtcblx0XHRcdH1cblx0XHR9IGVsc2Uge1xuXHRcdFx0dmFyIGtleXMgPSBfLmtleXMob2JqKTtcblx0XHRcdGZvciAoaSA9IDAsIGxlbmd0aCA9IGtleXMubGVuZ3RoOyBpIDwgbGVuZ3RoOyBpKyspIHtcblx0XHRcdFx0aXRlcmF0ZWUob2JqW2tleXNbaV1dLCBrZXlzW2ldLCBvYmopO1xuXHRcdFx0fVxuXHRcdH1cblx0XHRyZXR1cm4gb2JqO1xuXHR9O1xuXG5cdC8vIFJldHVybiB0aGUgcmVzdWx0cyBvZiBhcHBseWluZyB0aGUgaXRlcmF0ZWUgdG8gZWFjaCBlbGVtZW50LlxuXHRfLm1hcCA9IF8uY29sbGVjdCA9IGZ1bmN0aW9uIChvYmosIGl0ZXJhdGVlLCBjb250ZXh0KSB7XG5cdFx0aXRlcmF0ZWUgPSBjYihpdGVyYXRlZSwgY29udGV4dCk7XG5cdFx0dmFyIGtleXMgPSAhaXNBcnJheUxpa2Uob2JqKSAmJiBfLmtleXMob2JqKSxcblx0XHRcdGxlbmd0aCA9IChrZXlzIHx8IG9iaikubGVuZ3RoLFxuXHRcdFx0cmVzdWx0cyA9IEFycmF5KGxlbmd0aCk7XG5cdFx0Zm9yICh2YXIgaW5kZXggPSAwOyBpbmRleCA8IGxlbmd0aDsgaW5kZXgrKykge1xuXHRcdFx0dmFyIGN1cnJlbnRLZXkgPSBrZXlzID8ga2V5c1tpbmRleF0gOiBpbmRleDtcblx0XHRcdHJlc3VsdHNbaW5kZXhdID0gaXRlcmF0ZWUob2JqW2N1cnJlbnRLZXldLCBjdXJyZW50S2V5LCBvYmopO1xuXHRcdH1cblx0XHRyZXR1cm4gcmVzdWx0cztcblx0fTtcblxuXHQvLyBDcmVhdGUgYSByZWR1Y2luZyBmdW5jdGlvbiBpdGVyYXRpbmcgbGVmdCBvciByaWdodC5cblx0ZnVuY3Rpb24gY3JlYXRlUmVkdWNlKGRpcikge1xuXHRcdC8vIE9wdGltaXplZCBpdGVyYXRvciBmdW5jdGlvbiBhcyB1c2luZyBhcmd1bWVudHMubGVuZ3RoXG5cdFx0Ly8gaW4gdGhlIG1haW4gZnVuY3Rpb24gd2lsbCBkZW9wdGltaXplIHRoZSwgc2VlICMxOTkxLlxuXHRcdGZ1bmN0aW9uIGl0ZXJhdG9yKG9iaiwgaXRlcmF0ZWUsIG1lbW8sIGtleXMsIGluZGV4LCBsZW5ndGgpIHtcblx0XHRcdGZvciAoOyBpbmRleCA+PSAwICYmIGluZGV4IDwgbGVuZ3RoOyBpbmRleCArPSBkaXIpIHtcblx0XHRcdFx0dmFyIGN1cnJlbnRLZXkgPSBrZXlzID8ga2V5c1tpbmRleF0gOiBpbmRleDtcblx0XHRcdFx0bWVtbyA9IGl0ZXJhdGVlKG1lbW8sIG9ialtjdXJyZW50S2V5XSwgY3VycmVudEtleSwgb2JqKTtcblx0XHRcdH1cblx0XHRcdHJldHVybiBtZW1vO1xuXHRcdH1cblxuXHRcdHJldHVybiBmdW5jdGlvbiAob2JqLCBpdGVyYXRlZSwgbWVtbywgY29udGV4dCkge1xuXHRcdFx0aXRlcmF0ZWUgPSBvcHRpbWl6ZUNiKGl0ZXJhdGVlLCBjb250ZXh0LCA0KTtcblx0XHRcdHZhciBrZXlzID0gIWlzQXJyYXlMaWtlKG9iaikgJiYgXy5rZXlzKG9iaiksXG5cdFx0XHRcdGxlbmd0aCA9IChrZXlzIHx8IG9iaikubGVuZ3RoLFxuXHRcdFx0XHRpbmRleCA9IGRpciA+IDAgPyAwIDogbGVuZ3RoIC0gMTtcblx0XHRcdC8vIERldGVybWluZSB0aGUgaW5pdGlhbCB2YWx1ZSBpZiBub25lIGlzIHByb3ZpZGVkLlxuXHRcdFx0aWYgKGFyZ3VtZW50cy5sZW5ndGggPCAzKSB7XG5cdFx0XHRcdG1lbW8gPSBvYmpba2V5cyA/IGtleXNbaW5kZXhdIDogaW5kZXhdO1xuXHRcdFx0XHRpbmRleCArPSBkaXI7XG5cdFx0XHR9XG5cdFx0XHRyZXR1cm4gaXRlcmF0b3Iob2JqLCBpdGVyYXRlZSwgbWVtbywga2V5cywgaW5kZXgsIGxlbmd0aCk7XG5cdFx0fTtcblx0fVxuXG5cdC8vICoqUmVkdWNlKiogYnVpbGRzIHVwIGEgc2luZ2xlIHJlc3VsdCBmcm9tIGEgbGlzdCBvZiB2YWx1ZXMsIGFrYSBgaW5qZWN0YCxcblx0Ly8gb3IgYGZvbGRsYC5cblx0Xy5yZWR1Y2UgPSBfLmZvbGRsID0gXy5pbmplY3QgPSBjcmVhdGVSZWR1Y2UoMSk7XG5cblx0Ly8gVGhlIHJpZ2h0LWFzc29jaWF0aXZlIHZlcnNpb24gb2YgcmVkdWNlLCBhbHNvIGtub3duIGFzIGBmb2xkcmAuXG5cdF8ucmVkdWNlUmlnaHQgPSBfLmZvbGRyID0gY3JlYXRlUmVkdWNlKC0xKTtcblxuXHQvLyBSZXR1cm4gdGhlIGZpcnN0IHZhbHVlIHdoaWNoIHBhc3NlcyBhIHRydXRoIHRlc3QuIEFsaWFzZWQgYXMgYGRldGVjdGAuXG5cdF8uZmluZCA9IF8uZGV0ZWN0ID0gZnVuY3Rpb24gKG9iaiwgcHJlZGljYXRlLCBjb250ZXh0KSB7XG5cdFx0dmFyIGtleTtcblx0XHRpZiAoaXNBcnJheUxpa2Uob2JqKSkge1xuXHRcdFx0a2V5ID0gXy5maW5kSW5kZXgob2JqLCBwcmVkaWNhdGUsIGNvbnRleHQpO1xuXHRcdH0gZWxzZSB7XG5cdFx0XHRrZXkgPSBfLmZpbmRLZXkob2JqLCBwcmVkaWNhdGUsIGNvbnRleHQpO1xuXHRcdH1cblx0XHRpZiAoa2V5ICE9PSB2b2lkIDAgJiYga2V5ICE9PSAtMSkgcmV0dXJuIG9ialtrZXldO1xuXHR9O1xuXG5cdC8vIFJldHVybiBhbGwgdGhlIGVsZW1lbnRzIHRoYXQgcGFzcyBhIHRydXRoIHRlc3QuXG5cdC8vIEFsaWFzZWQgYXMgYHNlbGVjdGAuXG5cdF8uZmlsdGVyID0gXy5zZWxlY3QgPSBmdW5jdGlvbiAob2JqLCBwcmVkaWNhdGUsIGNvbnRleHQpIHtcblx0XHR2YXIgcmVzdWx0cyA9IFtdO1xuXHRcdHByZWRpY2F0ZSA9IGNiKHByZWRpY2F0ZSwgY29udGV4dCk7XG5cdFx0Xy5lYWNoKG9iaiwgZnVuY3Rpb24gKHZhbHVlLCBpbmRleCwgbGlzdCkge1xuXHRcdFx0aWYgKHByZWRpY2F0ZSh2YWx1ZSwgaW5kZXgsIGxpc3QpKSByZXN1bHRzLnB1c2godmFsdWUpO1xuXHRcdH0pO1xuXHRcdHJldHVybiByZXN1bHRzO1xuXHR9O1xuXG5cdC8vIFJldHVybiBhbGwgdGhlIGVsZW1lbnRzIGZvciB3aGljaCBhIHRydXRoIHRlc3QgZmFpbHMuXG5cdF8ucmVqZWN0ID0gZnVuY3Rpb24gKG9iaiwgcHJlZGljYXRlLCBjb250ZXh0KSB7XG5cdFx0cmV0dXJuIF8uZmlsdGVyKG9iaiwgXy5uZWdhdGUoY2IocHJlZGljYXRlKSksIGNvbnRleHQpO1xuXHR9O1xuXG5cdC8vIERldGVybWluZSB3aGV0aGVyIGFsbCBvZiB0aGUgZWxlbWVudHMgbWF0Y2ggYSB0cnV0aCB0ZXN0LlxuXHQvLyBBbGlhc2VkIGFzIGBhbGxgLlxuXHRfLmV2ZXJ5ID0gXy5hbGwgPSBmdW5jdGlvbiAob2JqLCBwcmVkaWNhdGUsIGNvbnRleHQpIHtcblx0XHRwcmVkaWNhdGUgPSBjYihwcmVkaWNhdGUsIGNvbnRleHQpO1xuXHRcdHZhciBrZXlzID0gIWlzQXJyYXlMaWtlKG9iaikgJiYgXy5rZXlzKG9iaiksXG5cdFx0XHRsZW5ndGggPSAoa2V5cyB8fCBvYmopLmxlbmd0aDtcblx0XHRmb3IgKHZhciBpbmRleCA9IDA7IGluZGV4IDwgbGVuZ3RoOyBpbmRleCsrKSB7XG5cdFx0XHR2YXIgY3VycmVudEtleSA9IGtleXMgPyBrZXlzW2luZGV4XSA6IGluZGV4O1xuXHRcdFx0aWYgKCFwcmVkaWNhdGUob2JqW2N1cnJlbnRLZXldLCBjdXJyZW50S2V5LCBvYmopKSByZXR1cm4gZmFsc2U7XG5cdFx0fVxuXHRcdHJldHVybiB0cnVlO1xuXHR9O1xuXG5cdC8vIERldGVybWluZSBpZiBhdCBsZWFzdCBvbmUgZWxlbWVudCBpbiB0aGUgb2JqZWN0IG1hdGNoZXMgYSB0cnV0aCB0ZXN0LlxuXHQvLyBBbGlhc2VkIGFzIGBhbnlgLlxuXHRfLnNvbWUgPSBfLmFueSA9IGZ1bmN0aW9uIChvYmosIHByZWRpY2F0ZSwgY29udGV4dCkge1xuXHRcdHByZWRpY2F0ZSA9IGNiKHByZWRpY2F0ZSwgY29udGV4dCk7XG5cdFx0dmFyIGtleXMgPSAhaXNBcnJheUxpa2Uob2JqKSAmJiBfLmtleXMob2JqKSxcblx0XHRcdGxlbmd0aCA9IChrZXlzIHx8IG9iaikubGVuZ3RoO1xuXHRcdGZvciAodmFyIGluZGV4ID0gMDsgaW5kZXggPCBsZW5ndGg7IGluZGV4KyspIHtcblx0XHRcdHZhciBjdXJyZW50S2V5ID0ga2V5cyA/IGtleXNbaW5kZXhdIDogaW5kZXg7XG5cdFx0XHRpZiAocHJlZGljYXRlKG9ialtjdXJyZW50S2V5XSwgY3VycmVudEtleSwgb2JqKSkgcmV0dXJuIHRydWU7XG5cdFx0fVxuXHRcdHJldHVybiBmYWxzZTtcblx0fTtcblxuXHQvLyBEZXRlcm1pbmUgaWYgdGhlIGFycmF5IG9yIG9iamVjdCBjb250YWlucyBhIGdpdmVuIHZhbHVlICh1c2luZyBgPT09YCkuXG5cdC8vIEFsaWFzZWQgYXMgYGluY2x1ZGVzYCBhbmQgYGluY2x1ZGVgLlxuXHRfLmNvbnRhaW5zID0gXy5pbmNsdWRlcyA9IF8uaW5jbHVkZSA9IGZ1bmN0aW9uIChvYmosIHRhcmdldCwgZnJvbUluZGV4KSB7XG5cdFx0aWYgKCFpc0FycmF5TGlrZShvYmopKSBvYmogPSBfLnZhbHVlcyhvYmopO1xuXHRcdHJldHVybiBfLmluZGV4T2Yob2JqLCB0YXJnZXQsIHR5cGVvZiBmcm9tSW5kZXggPT0gJ251bWJlcicgJiYgZnJvbUluZGV4KSA+PSAwO1xuXHR9O1xuXG5cdC8vIEludm9rZSBhIG1ldGhvZCAod2l0aCBhcmd1bWVudHMpIG9uIGV2ZXJ5IGl0ZW0gaW4gYSBjb2xsZWN0aW9uLlxuXHRfLmludm9rZSA9IGZ1bmN0aW9uIChvYmosIG1ldGhvZCkge1xuXHRcdHZhciBhcmdzID0gc2xpY2UuY2FsbChhcmd1bWVudHMsIDIpO1xuXHRcdHZhciBpc0Z1bmMgPSBfLmlzRnVuY3Rpb24obWV0aG9kKTtcblx0XHRyZXR1cm4gXy5tYXAob2JqLCBmdW5jdGlvbiAodmFsdWUpIHtcblx0XHRcdHZhciBmdW5jID0gaXNGdW5jID8gbWV0aG9kIDogdmFsdWVbbWV0aG9kXTtcblx0XHRcdHJldHVybiBmdW5jID09IG51bGwgPyBmdW5jIDogZnVuYy5hcHBseSh2YWx1ZSwgYXJncyk7XG5cdFx0fSk7XG5cdH07XG5cblx0Ly8gQ29udmVuaWVuY2UgdmVyc2lvbiBvZiBhIGNvbW1vbiB1c2UgY2FzZSBvZiBgbWFwYDogZmV0Y2hpbmcgYSBwcm9wZXJ0eS5cblx0Xy5wbHVjayA9IGZ1bmN0aW9uIChvYmosIGtleSkge1xuXHRcdHJldHVybiBfLm1hcChvYmosIF8ucHJvcGVydHkoa2V5KSk7XG5cdH07XG5cblx0Ly8gQ29udmVuaWVuY2UgdmVyc2lvbiBvZiBhIGNvbW1vbiB1c2UgY2FzZSBvZiBgZmlsdGVyYDogc2VsZWN0aW5nIG9ubHkgb2JqZWN0c1xuXHQvLyBjb250YWluaW5nIHNwZWNpZmljIGBrZXk6dmFsdWVgIHBhaXJzLlxuXHRfLndoZXJlID0gZnVuY3Rpb24gKG9iaiwgYXR0cnMpIHtcblx0XHRyZXR1cm4gXy5maWx0ZXIob2JqLCBfLm1hdGNoZXIoYXR0cnMpKTtcblx0fTtcblxuXHQvLyBDb252ZW5pZW5jZSB2ZXJzaW9uIG9mIGEgY29tbW9uIHVzZSBjYXNlIG9mIGBmaW5kYDogZ2V0dGluZyB0aGUgZmlyc3Qgb2JqZWN0XG5cdC8vIGNvbnRhaW5pbmcgc3BlY2lmaWMgYGtleTp2YWx1ZWAgcGFpcnMuXG5cdF8uZmluZFdoZXJlID0gZnVuY3Rpb24gKG9iaiwgYXR0cnMpIHtcblx0XHRyZXR1cm4gXy5maW5kKG9iaiwgXy5tYXRjaGVyKGF0dHJzKSk7XG5cdH07XG5cblx0Ly8gUmV0dXJuIHRoZSBtYXhpbXVtIGVsZW1lbnQgKG9yIGVsZW1lbnQtYmFzZWQgY29tcHV0YXRpb24pLlxuXHRfLm1heCA9IGZ1bmN0aW9uIChvYmosIGl0ZXJhdGVlLCBjb250ZXh0KSB7XG5cdFx0dmFyIHJlc3VsdCA9IC1JbmZpbml0eSwgbGFzdENvbXB1dGVkID0gLUluZmluaXR5LFxuXHRcdFx0dmFsdWUsIGNvbXB1dGVkO1xuXHRcdGlmIChpdGVyYXRlZSA9PSBudWxsICYmIG9iaiAhPSBudWxsKSB7XG5cdFx0XHRvYmogPSBpc0FycmF5TGlrZShvYmopID8gb2JqIDogXy52YWx1ZXMob2JqKTtcblx0XHRcdGZvciAodmFyIGkgPSAwLCBsZW5ndGggPSBvYmoubGVuZ3RoOyBpIDwgbGVuZ3RoOyBpKyspIHtcblx0XHRcdFx0dmFsdWUgPSBvYmpbaV07XG5cdFx0XHRcdGlmICh2YWx1ZSA+IHJlc3VsdCkge1xuXHRcdFx0XHRcdHJlc3VsdCA9IHZhbHVlO1xuXHRcdFx0XHR9XG5cdFx0XHR9XG5cdFx0fSBlbHNlIHtcblx0XHRcdGl0ZXJhdGVlID0gY2IoaXRlcmF0ZWUsIGNvbnRleHQpO1xuXHRcdFx0Xy5lYWNoKG9iaiwgZnVuY3Rpb24gKHZhbHVlLCBpbmRleCwgbGlzdCkge1xuXHRcdFx0XHRjb21wdXRlZCA9IGl0ZXJhdGVlKHZhbHVlLCBpbmRleCwgbGlzdCk7XG5cdFx0XHRcdGlmIChjb21wdXRlZCA+IGxhc3RDb21wdXRlZCB8fCBjb21wdXRlZCA9PT0gLUluZmluaXR5ICYmIHJlc3VsdCA9PT0gLUluZmluaXR5KSB7XG5cdFx0XHRcdFx0cmVzdWx0ID0gdmFsdWU7XG5cdFx0XHRcdFx0bGFzdENvbXB1dGVkID0gY29tcHV0ZWQ7XG5cdFx0XHRcdH1cblx0XHRcdH0pO1xuXHRcdH1cblx0XHRyZXR1cm4gcmVzdWx0O1xuXHR9O1xuXG5cdC8vIFJldHVybiB0aGUgbWluaW11bSBlbGVtZW50IChvciBlbGVtZW50LWJhc2VkIGNvbXB1dGF0aW9uKS5cblx0Xy5taW4gPSBmdW5jdGlvbiAob2JqLCBpdGVyYXRlZSwgY29udGV4dCkge1xuXHRcdHZhciByZXN1bHQgPSBJbmZpbml0eSwgbGFzdENvbXB1dGVkID0gSW5maW5pdHksXG5cdFx0XHR2YWx1ZSwgY29tcHV0ZWQ7XG5cdFx0aWYgKGl0ZXJhdGVlID09IG51bGwgJiYgb2JqICE9IG51bGwpIHtcblx0XHRcdG9iaiA9IGlzQXJyYXlMaWtlKG9iaikgPyBvYmogOiBfLnZhbHVlcyhvYmopO1xuXHRcdFx0Zm9yICh2YXIgaSA9IDAsIGxlbmd0aCA9IG9iai5sZW5ndGg7IGkgPCBsZW5ndGg7IGkrKykge1xuXHRcdFx0XHR2YWx1ZSA9IG9ialtpXTtcblx0XHRcdFx0aWYgKHZhbHVlIDwgcmVzdWx0KSB7XG5cdFx0XHRcdFx0cmVzdWx0ID0gdmFsdWU7XG5cdFx0XHRcdH1cblx0XHRcdH1cblx0XHR9IGVsc2Uge1xuXHRcdFx0aXRlcmF0ZWUgPSBjYihpdGVyYXRlZSwgY29udGV4dCk7XG5cdFx0XHRfLmVhY2gob2JqLCBmdW5jdGlvbiAodmFsdWUsIGluZGV4LCBsaXN0KSB7XG5cdFx0XHRcdGNvbXB1dGVkID0gaXRlcmF0ZWUodmFsdWUsIGluZGV4LCBsaXN0KTtcblx0XHRcdFx0aWYgKGNvbXB1dGVkIDwgbGFzdENvbXB1dGVkIHx8IGNvbXB1dGVkID09PSBJbmZpbml0eSAmJiByZXN1bHQgPT09IEluZmluaXR5KSB7XG5cdFx0XHRcdFx0cmVzdWx0ID0gdmFsdWU7XG5cdFx0XHRcdFx0bGFzdENvbXB1dGVkID0gY29tcHV0ZWQ7XG5cdFx0XHRcdH1cblx0XHRcdH0pO1xuXHRcdH1cblx0XHRyZXR1cm4gcmVzdWx0O1xuXHR9O1xuXG5cdC8vIFNodWZmbGUgYSBjb2xsZWN0aW9uLCB1c2luZyB0aGUgbW9kZXJuIHZlcnNpb24gb2YgdGhlXG5cdC8vIFtGaXNoZXItWWF0ZXMgc2h1ZmZsZV0oaHR0cDovL2VuLndpa2lwZWRpYS5vcmcvd2lraS9GaXNoZXLpiKXmj6dhdGVzX3NodWZmbGUpLlxuXHRfLnNodWZmbGUgPSBmdW5jdGlvbiAob2JqKSB7XG5cdFx0dmFyIHNldCA9IGlzQXJyYXlMaWtlKG9iaikgPyBvYmogOiBfLnZhbHVlcyhvYmopO1xuXHRcdHZhciBsZW5ndGggPSBzZXQubGVuZ3RoO1xuXHRcdHZhciBzaHVmZmxlZCA9IEFycmF5KGxlbmd0aCk7XG5cdFx0Zm9yICh2YXIgaW5kZXggPSAwLCByYW5kOyBpbmRleCA8IGxlbmd0aDsgaW5kZXgrKykge1xuXHRcdFx0cmFuZCA9IF8ucmFuZG9tKDAsIGluZGV4KTtcblx0XHRcdGlmIChyYW5kICE9PSBpbmRleCkgc2h1ZmZsZWRbaW5kZXhdID0gc2h1ZmZsZWRbcmFuZF07XG5cdFx0XHRzaHVmZmxlZFtyYW5kXSA9IHNldFtpbmRleF07XG5cdFx0fVxuXHRcdHJldHVybiBzaHVmZmxlZDtcblx0fTtcblxuXHQvLyBTYW1wbGUgKipuKiogcmFuZG9tIHZhbHVlcyBmcm9tIGEgY29sbGVjdGlvbi5cblx0Ly8gSWYgKipuKiogaXMgbm90IHNwZWNpZmllZCwgcmV0dXJucyBhIHNpbmdsZSByYW5kb20gZWxlbWVudC5cblx0Ly8gVGhlIGludGVybmFsIGBndWFyZGAgYXJndW1lbnQgYWxsb3dzIGl0IHRvIHdvcmsgd2l0aCBgbWFwYC5cblx0Xy5zYW1wbGUgPSBmdW5jdGlvbiAob2JqLCBuLCBndWFyZCkge1xuXHRcdGlmIChuID09IG51bGwgfHwgZ3VhcmQpIHtcblx0XHRcdGlmICghaXNBcnJheUxpa2Uob2JqKSkgb2JqID0gXy52YWx1ZXMob2JqKTtcblx0XHRcdHJldHVybiBvYmpbXy5yYW5kb20ob2JqLmxlbmd0aCAtIDEpXTtcblx0XHR9XG5cdFx0cmV0dXJuIF8uc2h1ZmZsZShvYmopLnNsaWNlKDAsIE1hdGgubWF4KDAsIG4pKTtcblx0fTtcblxuXHQvLyBTb3J0IHRoZSBvYmplY3QncyB2YWx1ZXMgYnkgYSBjcml0ZXJpb24gcHJvZHVjZWQgYnkgYW4gaXRlcmF0ZWUuXG5cdF8uc29ydEJ5ID0gZnVuY3Rpb24gKG9iaiwgaXRlcmF0ZWUsIGNvbnRleHQpIHtcblx0XHRpdGVyYXRlZSA9IGNiKGl0ZXJhdGVlLCBjb250ZXh0KTtcblx0XHRyZXR1cm4gXy5wbHVjayhfLm1hcChvYmosIGZ1bmN0aW9uICh2YWx1ZSwgaW5kZXgsIGxpc3QpIHtcblx0XHRcdHJldHVybiB7XG5cdFx0XHRcdHZhbHVlOiB2YWx1ZSxcblx0XHRcdFx0aW5kZXg6IGluZGV4LFxuXHRcdFx0XHRjcml0ZXJpYTogaXRlcmF0ZWUodmFsdWUsIGluZGV4LCBsaXN0KVxuXHRcdFx0fTtcblx0XHR9KS5zb3J0KGZ1bmN0aW9uIChsZWZ0LCByaWdodCkge1xuXHRcdFx0dmFyIGEgPSBsZWZ0LmNyaXRlcmlhO1xuXHRcdFx0dmFyIGIgPSByaWdodC5jcml0ZXJpYTtcblx0XHRcdGlmIChhICE9PSBiKSB7XG5cdFx0XHRcdGlmIChhID4gYiB8fCBhID09PSB2b2lkIDApIHJldHVybiAxO1xuXHRcdFx0XHRpZiAoYSA8IGIgfHwgYiA9PT0gdm9pZCAwKSByZXR1cm4gLTE7XG5cdFx0XHR9XG5cdFx0XHRyZXR1cm4gbGVmdC5pbmRleCAtIHJpZ2h0LmluZGV4O1xuXHRcdH0pLCAndmFsdWUnKTtcblx0fTtcblxuXHQvLyBBbiBpbnRlcm5hbCBmdW5jdGlvbiB1c2VkIGZvciBhZ2dyZWdhdGUgXCJncm91cCBieVwiIG9wZXJhdGlvbnMuXG5cdHZhciBncm91cCA9IGZ1bmN0aW9uIChiZWhhdmlvcikge1xuXHRcdHJldHVybiBmdW5jdGlvbiAob2JqLCBpdGVyYXRlZSwgY29udGV4dCkge1xuXHRcdFx0dmFyIHJlc3VsdCA9IHt9O1xuXHRcdFx0aXRlcmF0ZWUgPSBjYihpdGVyYXRlZSwgY29udGV4dCk7XG5cdFx0XHRfLmVhY2gob2JqLCBmdW5jdGlvbiAodmFsdWUsIGluZGV4KSB7XG5cdFx0XHRcdHZhciBrZXkgPSBpdGVyYXRlZSh2YWx1ZSwgaW5kZXgsIG9iaik7XG5cdFx0XHRcdGJlaGF2aW9yKHJlc3VsdCwgdmFsdWUsIGtleSk7XG5cdFx0XHR9KTtcblx0XHRcdHJldHVybiByZXN1bHQ7XG5cdFx0fTtcblx0fTtcblxuXHQvLyBHcm91cHMgdGhlIG9iamVjdCdzIHZhbHVlcyBieSBhIGNyaXRlcmlvbi4gUGFzcyBlaXRoZXIgYSBzdHJpbmcgYXR0cmlidXRlXG5cdC8vIHRvIGdyb3VwIGJ5LCBvciBhIGZ1bmN0aW9uIHRoYXQgcmV0dXJucyB0aGUgY3JpdGVyaW9uLlxuXHRfLmdyb3VwQnkgPSBncm91cChmdW5jdGlvbiAocmVzdWx0LCB2YWx1ZSwga2V5KSB7XG5cdFx0aWYgKF8uaGFzKHJlc3VsdCwga2V5KSkgcmVzdWx0W2tleV0ucHVzaCh2YWx1ZSk7IGVsc2UgcmVzdWx0W2tleV0gPSBbdmFsdWVdO1xuXHR9KTtcblxuXHQvLyBJbmRleGVzIHRoZSBvYmplY3QncyB2YWx1ZXMgYnkgYSBjcml0ZXJpb24sIHNpbWlsYXIgdG8gYGdyb3VwQnlgLCBidXQgZm9yXG5cdC8vIHdoZW4geW91IGtub3cgdGhhdCB5b3VyIGluZGV4IHZhbHVlcyB3aWxsIGJlIHVuaXF1ZS5cblx0Xy5pbmRleEJ5ID0gZ3JvdXAoZnVuY3Rpb24gKHJlc3VsdCwgdmFsdWUsIGtleSkge1xuXHRcdHJlc3VsdFtrZXldID0gdmFsdWU7XG5cdH0pO1xuXG5cdC8vIENvdW50cyBpbnN0YW5jZXMgb2YgYW4gb2JqZWN0IHRoYXQgZ3JvdXAgYnkgYSBjZXJ0YWluIGNyaXRlcmlvbi4gUGFzc1xuXHQvLyBlaXRoZXIgYSBzdHJpbmcgYXR0cmlidXRlIHRvIGNvdW50IGJ5LCBvciBhIGZ1bmN0aW9uIHRoYXQgcmV0dXJucyB0aGVcblx0Ly8gY3JpdGVyaW9uLlxuXHRfLmNvdW50QnkgPSBncm91cChmdW5jdGlvbiAocmVzdWx0LCB2YWx1ZSwga2V5KSB7XG5cdFx0aWYgKF8uaGFzKHJlc3VsdCwga2V5KSkgcmVzdWx0W2tleV0rKzsgZWxzZSByZXN1bHRba2V5XSA9IDE7XG5cdH0pO1xuXG5cdC8vIFNhZmVseSBjcmVhdGUgYSByZWFsLCBsaXZlIGFycmF5IGZyb20gYW55dGhpbmcgaXRlcmFibGUuXG5cdF8udG9BcnJheSA9IGZ1bmN0aW9uIChvYmopIHtcblx0XHRpZiAoIW9iaikgcmV0dXJuIFtdO1xuXHRcdGlmIChfLmlzQXJyYXkob2JqKSkgcmV0dXJuIHNsaWNlLmNhbGwob2JqKTtcblx0XHRpZiAoaXNBcnJheUxpa2Uob2JqKSkgcmV0dXJuIF8ubWFwKG9iaiwgXy5pZGVudGl0eSk7XG5cdFx0cmV0dXJuIF8udmFsdWVzKG9iaik7XG5cdH07XG5cblx0Ly8gUmV0dXJuIHRoZSBudW1iZXIgb2YgZWxlbWVudHMgaW4gYW4gb2JqZWN0LlxuXHRfLnNpemUgPSBmdW5jdGlvbiAob2JqKSB7XG5cdFx0aWYgKG9iaiA9PSBudWxsKSByZXR1cm4gMDtcblx0XHRyZXR1cm4gaXNBcnJheUxpa2Uob2JqKSA/IG9iai5sZW5ndGggOiBfLmtleXMob2JqKS5sZW5ndGg7XG5cdH07XG5cblx0Ly8gU3BsaXQgYSBjb2xsZWN0aW9uIGludG8gdHdvIGFycmF5czogb25lIHdob3NlIGVsZW1lbnRzIGFsbCBzYXRpc2Z5IHRoZSBnaXZlblxuXHQvLyBwcmVkaWNhdGUsIGFuZCBvbmUgd2hvc2UgZWxlbWVudHMgYWxsIGRvIG5vdCBzYXRpc2Z5IHRoZSBwcmVkaWNhdGUuXG5cdF8ucGFydGl0aW9uID0gZnVuY3Rpb24gKG9iaiwgcHJlZGljYXRlLCBjb250ZXh0KSB7XG5cdFx0cHJlZGljYXRlID0gY2IocHJlZGljYXRlLCBjb250ZXh0KTtcblx0XHR2YXIgcGFzcyA9IFtdLCBmYWlsID0gW107XG5cdFx0Xy5lYWNoKG9iaiwgZnVuY3Rpb24gKHZhbHVlLCBrZXksIG9iaikge1xuXHRcdFx0KHByZWRpY2F0ZSh2YWx1ZSwga2V5LCBvYmopID8gcGFzcyA6IGZhaWwpLnB1c2godmFsdWUpO1xuXHRcdH0pO1xuXHRcdHJldHVybiBbcGFzcywgZmFpbF07XG5cdH07XG5cblx0Ly8gQXJyYXkgRnVuY3Rpb25zXG5cdC8vIC0tLS0tLS0tLS0tLS0tLVxuXG5cdC8vIEdldCB0aGUgZmlyc3QgZWxlbWVudCBvZiBhbiBhcnJheS4gUGFzc2luZyAqKm4qKiB3aWxsIHJldHVybiB0aGUgZmlyc3QgTlxuXHQvLyB2YWx1ZXMgaW4gdGhlIGFycmF5LiBBbGlhc2VkIGFzIGBoZWFkYCBhbmQgYHRha2VgLiBUaGUgKipndWFyZCoqIGNoZWNrXG5cdC8vIGFsbG93cyBpdCB0byB3b3JrIHdpdGggYF8ubWFwYC5cblx0Xy5maXJzdCA9IF8uaGVhZCA9IF8udGFrZSA9IGZ1bmN0aW9uIChhcnJheSwgbiwgZ3VhcmQpIHtcblx0XHRpZiAoYXJyYXkgPT0gbnVsbCkgcmV0dXJuIHZvaWQgMDtcblx0XHRpZiAobiA9PSBudWxsIHx8IGd1YXJkKSByZXR1cm4gYXJyYXlbMF07XG5cdFx0cmV0dXJuIF8uaW5pdGlhbChhcnJheSwgYXJyYXkubGVuZ3RoIC0gbik7XG5cdH07XG5cblx0Ly8gUmV0dXJucyBldmVyeXRoaW5nIGJ1dCB0aGUgbGFzdCBlbnRyeSBvZiB0aGUgYXJyYXkuIEVzcGVjaWFsbHkgdXNlZnVsIG9uXG5cdC8vIHRoZSBhcmd1bWVudHMgb2JqZWN0LiBQYXNzaW5nICoqbioqIHdpbGwgcmV0dXJuIGFsbCB0aGUgdmFsdWVzIGluXG5cdC8vIHRoZSBhcnJheSwgZXhjbHVkaW5nIHRoZSBsYXN0IE4uXG5cdF8uaW5pdGlhbCA9IGZ1bmN0aW9uIChhcnJheSwgbiwgZ3VhcmQpIHtcblx0XHRyZXR1cm4gc2xpY2UuY2FsbChhcnJheSwgMCwgTWF0aC5tYXgoMCwgYXJyYXkubGVuZ3RoIC0gKG4gPT0gbnVsbCB8fCBndWFyZCA/IDEgOiBuKSkpO1xuXHR9O1xuXG5cdC8vIEdldCB0aGUgbGFzdCBlbGVtZW50IG9mIGFuIGFycmF5LiBQYXNzaW5nICoqbioqIHdpbGwgcmV0dXJuIHRoZSBsYXN0IE5cblx0Ly8gdmFsdWVzIGluIHRoZSBhcnJheS5cblx0Xy5sYXN0ID0gZnVuY3Rpb24gKGFycmF5LCBuLCBndWFyZCkge1xuXHRcdGlmIChhcnJheSA9PSBudWxsKSByZXR1cm4gdm9pZCAwO1xuXHRcdGlmIChuID09IG51bGwgfHwgZ3VhcmQpIHJldHVybiBhcnJheVthcnJheS5sZW5ndGggLSAxXTtcblx0XHRyZXR1cm4gXy5yZXN0KGFycmF5LCBNYXRoLm1heCgwLCBhcnJheS5sZW5ndGggLSBuKSk7XG5cdH07XG5cblx0Ly8gUmV0dXJucyBldmVyeXRoaW5nIGJ1dCB0aGUgZmlyc3QgZW50cnkgb2YgdGhlIGFycmF5LiBBbGlhc2VkIGFzIGB0YWlsYCBhbmQgYGRyb3BgLlxuXHQvLyBFc3BlY2lhbGx5IHVzZWZ1bCBvbiB0aGUgYXJndW1lbnRzIG9iamVjdC4gUGFzc2luZyBhbiAqKm4qKiB3aWxsIHJldHVyblxuXHQvLyB0aGUgcmVzdCBOIHZhbHVlcyBpbiB0aGUgYXJyYXkuXG5cdF8ucmVzdCA9IF8udGFpbCA9IF8uZHJvcCA9IGZ1bmN0aW9uIChhcnJheSwgbiwgZ3VhcmQpIHtcblx0XHRyZXR1cm4gc2xpY2UuY2FsbChhcnJheSwgbiA9PSBudWxsIHx8IGd1YXJkID8gMSA6IG4pO1xuXHR9O1xuXG5cdC8vIFRyaW0gb3V0IGFsbCBmYWxzeSB2YWx1ZXMgZnJvbSBhbiBhcnJheS5cblx0Xy5jb21wYWN0ID0gZnVuY3Rpb24gKGFycmF5KSB7XG5cdFx0cmV0dXJuIF8uZmlsdGVyKGFycmF5LCBfLmlkZW50aXR5KTtcblx0fTtcblxuXHQvLyBJbnRlcm5hbCBpbXBsZW1lbnRhdGlvbiBvZiBhIHJlY3Vyc2l2ZSBgZmxhdHRlbmAgZnVuY3Rpb24uXG5cdHZhciBmbGF0dGVuID0gZnVuY3Rpb24gKGlucHV0LCBzaGFsbG93LCBzdHJpY3QsIHN0YXJ0SW5kZXgpIHtcblx0XHR2YXIgb3V0cHV0ID0gW10sIGlkeCA9IDA7XG5cdFx0Zm9yICh2YXIgaSA9IHN0YXJ0SW5kZXggfHwgMCwgbGVuZ3RoID0gaW5wdXQgJiYgaW5wdXQubGVuZ3RoOyBpIDwgbGVuZ3RoOyBpKyspIHtcblx0XHRcdHZhciB2YWx1ZSA9IGlucHV0W2ldO1xuXHRcdFx0aWYgKGlzQXJyYXlMaWtlKHZhbHVlKSAmJiAoXy5pc0FycmF5KHZhbHVlKSB8fCBfLmlzQXJndW1lbnRzKHZhbHVlKSkpIHtcblx0XHRcdFx0Ly9mbGF0dGVuIGN1cnJlbnQgbGV2ZWwgb2YgYXJyYXkgb3IgYXJndW1lbnRzIG9iamVjdFxuXHRcdFx0XHRpZiAoIXNoYWxsb3cpIHZhbHVlID0gZmxhdHRlbih2YWx1ZSwgc2hhbGxvdywgc3RyaWN0KTtcblx0XHRcdFx0dmFyIGogPSAwLCBsZW4gPSB2YWx1ZS5sZW5ndGg7XG5cdFx0XHRcdG91dHB1dC5sZW5ndGggKz0gbGVuO1xuXHRcdFx0XHR3aGlsZSAoaiA8IGxlbikge1xuXHRcdFx0XHRcdG91dHB1dFtpZHgrK10gPSB2YWx1ZVtqKytdO1xuXHRcdFx0XHR9XG5cdFx0XHR9IGVsc2UgaWYgKCFzdHJpY3QpIHtcblx0XHRcdFx0b3V0cHV0W2lkeCsrXSA9IHZhbHVlO1xuXHRcdFx0fVxuXHRcdH1cblx0XHRyZXR1cm4gb3V0cHV0O1xuXHR9O1xuXG5cdC8vIEZsYXR0ZW4gb3V0IGFuIGFycmF5LCBlaXRoZXIgcmVjdXJzaXZlbHkgKGJ5IGRlZmF1bHQpLCBvciBqdXN0IG9uZSBsZXZlbC5cblx0Xy5mbGF0dGVuID0gZnVuY3Rpb24gKGFycmF5LCBzaGFsbG93KSB7XG5cdFx0cmV0dXJuIGZsYXR0ZW4oYXJyYXksIHNoYWxsb3csIGZhbHNlKTtcblx0fTtcblxuXHQvLyBSZXR1cm4gYSB2ZXJzaW9uIG9mIHRoZSBhcnJheSB0aGF0IGRvZXMgbm90IGNvbnRhaW4gdGhlIHNwZWNpZmllZCB2YWx1ZShzKS5cblx0Xy53aXRob3V0ID0gZnVuY3Rpb24gKGFycmF5KSB7XG5cdFx0cmV0dXJuIF8uZGlmZmVyZW5jZShhcnJheSwgc2xpY2UuY2FsbChhcmd1bWVudHMsIDEpKTtcblx0fTtcblxuXHQvLyBQcm9kdWNlIGEgZHVwbGljYXRlLWZyZWUgdmVyc2lvbiBvZiB0aGUgYXJyYXkuIElmIHRoZSBhcnJheSBoYXMgYWxyZWFkeVxuXHQvLyBiZWVuIHNvcnRlZCwgeW91IGhhdmUgdGhlIG9wdGlvbiBvZiB1c2luZyBhIGZhc3RlciBhbGdvcml0aG0uXG5cdC8vIEFsaWFzZWQgYXMgYHVuaXF1ZWAuXG5cdF8udW5pcSA9IF8udW5pcXVlID0gZnVuY3Rpb24gKGFycmF5LCBpc1NvcnRlZCwgaXRlcmF0ZWUsIGNvbnRleHQpIHtcblx0XHRpZiAoYXJyYXkgPT0gbnVsbCkgcmV0dXJuIFtdO1xuXHRcdGlmICghXy5pc0Jvb2xlYW4oaXNTb3J0ZWQpKSB7XG5cdFx0XHRjb250ZXh0ID0gaXRlcmF0ZWU7XG5cdFx0XHRpdGVyYXRlZSA9IGlzU29ydGVkO1xuXHRcdFx0aXNTb3J0ZWQgPSBmYWxzZTtcblx0XHR9XG5cdFx0aWYgKGl0ZXJhdGVlICE9IG51bGwpIGl0ZXJhdGVlID0gY2IoaXRlcmF0ZWUsIGNvbnRleHQpO1xuXHRcdHZhciByZXN1bHQgPSBbXTtcblx0XHR2YXIgc2VlbiA9IFtdO1xuXHRcdGZvciAodmFyIGkgPSAwLCBsZW5ndGggPSBhcnJheS5sZW5ndGg7IGkgPCBsZW5ndGg7IGkrKykge1xuXHRcdFx0dmFyIHZhbHVlID0gYXJyYXlbaV0sXG5cdFx0XHRcdGNvbXB1dGVkID0gaXRlcmF0ZWUgPyBpdGVyYXRlZSh2YWx1ZSwgaSwgYXJyYXkpIDogdmFsdWU7XG5cdFx0XHRpZiAoaXNTb3J0ZWQpIHtcblx0XHRcdFx0aWYgKCFpIHx8IHNlZW4gIT09IGNvbXB1dGVkKSByZXN1bHQucHVzaCh2YWx1ZSk7XG5cdFx0XHRcdHNlZW4gPSBjb21wdXRlZDtcblx0XHRcdH0gZWxzZSBpZiAoaXRlcmF0ZWUpIHtcblx0XHRcdFx0aWYgKCFfLmNvbnRhaW5zKHNlZW4sIGNvbXB1dGVkKSkge1xuXHRcdFx0XHRcdHNlZW4ucHVzaChjb21wdXRlZCk7XG5cdFx0XHRcdFx0cmVzdWx0LnB1c2godmFsdWUpO1xuXHRcdFx0XHR9XG5cdFx0XHR9IGVsc2UgaWYgKCFfLmNvbnRhaW5zKHJlc3VsdCwgdmFsdWUpKSB7XG5cdFx0XHRcdHJlc3VsdC5wdXNoKHZhbHVlKTtcblx0XHRcdH1cblx0XHR9XG5cdFx0cmV0dXJuIHJlc3VsdDtcblx0fTtcblxuXHQvLyBQcm9kdWNlIGFuIGFycmF5IHRoYXQgY29udGFpbnMgdGhlIHVuaW9uOiBlYWNoIGRpc3RpbmN0IGVsZW1lbnQgZnJvbSBhbGwgb2Zcblx0Ly8gdGhlIHBhc3NlZC1pbiBhcnJheXMuXG5cdF8udW5pb24gPSBmdW5jdGlvbiAoKSB7XG5cdFx0cmV0dXJuIF8udW5pcShmbGF0dGVuKGFyZ3VtZW50cywgdHJ1ZSwgdHJ1ZSkpO1xuXHR9O1xuXG5cdC8vIFByb2R1Y2UgYW4gYXJyYXkgdGhhdCBjb250YWlucyBldmVyeSBpdGVtIHNoYXJlZCBiZXR3ZWVuIGFsbCB0aGVcblx0Ly8gcGFzc2VkLWluIGFycmF5cy5cblx0Xy5pbnRlcnNlY3Rpb24gPSBmdW5jdGlvbiAoYXJyYXkpIHtcblx0XHRpZiAoYXJyYXkgPT0gbnVsbCkgcmV0dXJuIFtdO1xuXHRcdHZhciByZXN1bHQgPSBbXTtcblx0XHR2YXIgYXJnc0xlbmd0aCA9IGFyZ3VtZW50cy5sZW5ndGg7XG5cdFx0Zm9yICh2YXIgaSA9IDAsIGxlbmd0aCA9IGFycmF5Lmxlbmd0aDsgaSA8IGxlbmd0aDsgaSsrKSB7XG5cdFx0XHR2YXIgaXRlbSA9IGFycmF5W2ldO1xuXHRcdFx0aWYgKF8uY29udGFpbnMocmVzdWx0LCBpdGVtKSkgY29udGludWU7XG5cdFx0XHRmb3IgKHZhciBqID0gMTsgaiA8IGFyZ3NMZW5ndGg7IGorKykge1xuXHRcdFx0XHRpZiAoIV8uY29udGFpbnMoYXJndW1lbnRzW2pdLCBpdGVtKSkgYnJlYWs7XG5cdFx0XHR9XG5cdFx0XHRpZiAoaiA9PT0gYXJnc0xlbmd0aCkgcmVzdWx0LnB1c2goaXRlbSk7XG5cdFx0fVxuXHRcdHJldHVybiByZXN1bHQ7XG5cdH07XG5cblx0Ly8gVGFrZSB0aGUgZGlmZmVyZW5jZSBiZXR3ZWVuIG9uZSBhcnJheSBhbmQgYSBudW1iZXIgb2Ygb3RoZXIgYXJyYXlzLlxuXHQvLyBPbmx5IHRoZSBlbGVtZW50cyBwcmVzZW50IGluIGp1c3QgdGhlIGZpcnN0IGFycmF5IHdpbGwgcmVtYWluLlxuXHRfLmRpZmZlcmVuY2UgPSBmdW5jdGlvbiAoYXJyYXkpIHtcblx0XHR2YXIgcmVzdCA9IGZsYXR0ZW4oYXJndW1lbnRzLCB0cnVlLCB0cnVlLCAxKTtcblx0XHRyZXR1cm4gXy5maWx0ZXIoYXJyYXksIGZ1bmN0aW9uICh2YWx1ZSkge1xuXHRcdFx0cmV0dXJuICFfLmNvbnRhaW5zKHJlc3QsIHZhbHVlKTtcblx0XHR9KTtcblx0fTtcblxuXHQvLyBaaXAgdG9nZXRoZXIgbXVsdGlwbGUgbGlzdHMgaW50byBhIHNpbmdsZSBhcnJheSAtLSBlbGVtZW50cyB0aGF0IHNoYXJlXG5cdC8vIGFuIGluZGV4IGdvIHRvZ2V0aGVyLlxuXHRfLnppcCA9IGZ1bmN0aW9uICgpIHtcblx0XHRyZXR1cm4gXy51bnppcChhcmd1bWVudHMpO1xuXHR9O1xuXG5cdC8vIENvbXBsZW1lbnQgb2YgXy56aXAuIFVuemlwIGFjY2VwdHMgYW4gYXJyYXkgb2YgYXJyYXlzIGFuZCBncm91cHNcblx0Ly8gZWFjaCBhcnJheSdzIGVsZW1lbnRzIG9uIHNoYXJlZCBpbmRpY2VzXG5cdF8udW56aXAgPSBmdW5jdGlvbiAoYXJyYXkpIHtcblx0XHR2YXIgbGVuZ3RoID0gYXJyYXkgJiYgXy5tYXgoYXJyYXksICdsZW5ndGgnKS5sZW5ndGggfHwgMDtcblx0XHR2YXIgcmVzdWx0ID0gQXJyYXkobGVuZ3RoKTtcblxuXHRcdGZvciAodmFyIGluZGV4ID0gMDsgaW5kZXggPCBsZW5ndGg7IGluZGV4KyspIHtcblx0XHRcdHJlc3VsdFtpbmRleF0gPSBfLnBsdWNrKGFycmF5LCBpbmRleCk7XG5cdFx0fVxuXHRcdHJldHVybiByZXN1bHQ7XG5cdH07XG5cblx0Ly8gQ29udmVydHMgbGlzdHMgaW50byBvYmplY3RzLiBQYXNzIGVpdGhlciBhIHNpbmdsZSBhcnJheSBvZiBgW2tleSwgdmFsdWVdYFxuXHQvLyBwYWlycywgb3IgdHdvIHBhcmFsbGVsIGFycmF5cyBvZiB0aGUgc2FtZSBsZW5ndGggLS0gb25lIG9mIGtleXMsIGFuZCBvbmUgb2Zcblx0Ly8gdGhlIGNvcnJlc3BvbmRpbmcgdmFsdWVzLlxuXHRfLm9iamVjdCA9IGZ1bmN0aW9uIChsaXN0LCB2YWx1ZXMpIHtcblx0XHR2YXIgcmVzdWx0ID0ge307XG5cdFx0Zm9yICh2YXIgaSA9IDAsIGxlbmd0aCA9IGxpc3QgJiYgbGlzdC5sZW5ndGg7IGkgPCBsZW5ndGg7IGkrKykge1xuXHRcdFx0aWYgKHZhbHVlcykge1xuXHRcdFx0XHRyZXN1bHRbbGlzdFtpXV0gPSB2YWx1ZXNbaV07XG5cdFx0XHR9IGVsc2Uge1xuXHRcdFx0XHRyZXN1bHRbbGlzdFtpXVswXV0gPSBsaXN0W2ldWzFdO1xuXHRcdFx0fVxuXHRcdH1cblx0XHRyZXR1cm4gcmVzdWx0O1xuXHR9O1xuXG5cdC8vIFJldHVybiB0aGUgcG9zaXRpb24gb2YgdGhlIGZpcnN0IG9jY3VycmVuY2Ugb2YgYW4gaXRlbSBpbiBhbiBhcnJheSxcblx0Ly8gb3IgLTEgaWYgdGhlIGl0ZW0gaXMgbm90IGluY2x1ZGVkIGluIHRoZSBhcnJheS5cblx0Ly8gSWYgdGhlIGFycmF5IGlzIGxhcmdlIGFuZCBhbHJlYWR5IGluIHNvcnQgb3JkZXIsIHBhc3MgYHRydWVgXG5cdC8vIGZvciAqKmlzU29ydGVkKiogdG8gdXNlIGJpbmFyeSBzZWFyY2guXG5cdF8uaW5kZXhPZiA9IGZ1bmN0aW9uIChhcnJheSwgaXRlbSwgaXNTb3J0ZWQpIHtcblx0XHR2YXIgaSA9IDAsIGxlbmd0aCA9IGFycmF5ICYmIGFycmF5Lmxlbmd0aDtcblx0XHRpZiAodHlwZW9mIGlzU29ydGVkID09ICdudW1iZXInKSB7XG5cdFx0XHRpID0gaXNTb3J0ZWQgPCAwID8gTWF0aC5tYXgoMCwgbGVuZ3RoICsgaXNTb3J0ZWQpIDogaXNTb3J0ZWQ7XG5cdFx0fSBlbHNlIGlmIChpc1NvcnRlZCAmJiBsZW5ndGgpIHtcblx0XHRcdGkgPSBfLnNvcnRlZEluZGV4KGFycmF5LCBpdGVtKTtcblx0XHRcdHJldHVybiBhcnJheVtpXSA9PT0gaXRlbSA/IGkgOiAtMTtcblx0XHR9XG5cdFx0aWYgKGl0ZW0gIT09IGl0ZW0pIHtcblx0XHRcdHJldHVybiBfLmZpbmRJbmRleChzbGljZS5jYWxsKGFycmF5LCBpKSwgXy5pc05hTik7XG5cdFx0fVxuXHRcdGZvciAoOyBpIDwgbGVuZ3RoOyBpKyspIGlmIChhcnJheVtpXSA9PT0gaXRlbSkgcmV0dXJuIGk7XG5cdFx0cmV0dXJuIC0xO1xuXHR9O1xuXG5cdF8ubGFzdEluZGV4T2YgPSBmdW5jdGlvbiAoYXJyYXksIGl0ZW0sIGZyb20pIHtcblx0XHR2YXIgaWR4ID0gYXJyYXkgPyBhcnJheS5sZW5ndGggOiAwO1xuXHRcdGlmICh0eXBlb2YgZnJvbSA9PSAnbnVtYmVyJykge1xuXHRcdFx0aWR4ID0gZnJvbSA8IDAgPyBpZHggKyBmcm9tICsgMSA6IE1hdGgubWluKGlkeCwgZnJvbSArIDEpO1xuXHRcdH1cblx0XHRpZiAoaXRlbSAhPT0gaXRlbSkge1xuXHRcdFx0cmV0dXJuIF8uZmluZExhc3RJbmRleChzbGljZS5jYWxsKGFycmF5LCAwLCBpZHgpLCBfLmlzTmFOKTtcblx0XHR9XG5cdFx0d2hpbGUgKC0taWR4ID49IDApIGlmIChhcnJheVtpZHhdID09PSBpdGVtKSByZXR1cm4gaWR4O1xuXHRcdHJldHVybiAtMTtcblx0fTtcblxuXHQvLyBHZW5lcmF0b3IgZnVuY3Rpb24gdG8gY3JlYXRlIHRoZSBmaW5kSW5kZXggYW5kIGZpbmRMYXN0SW5kZXggZnVuY3Rpb25zXG5cdGZ1bmN0aW9uIGNyZWF0ZUluZGV4RmluZGVyKGRpcikge1xuXHRcdHJldHVybiBmdW5jdGlvbiAoYXJyYXksIHByZWRpY2F0ZSwgY29udGV4dCkge1xuXHRcdFx0cHJlZGljYXRlID0gY2IocHJlZGljYXRlLCBjb250ZXh0KTtcblx0XHRcdHZhciBsZW5ndGggPSBhcnJheSAhPSBudWxsICYmIGFycmF5Lmxlbmd0aDtcblx0XHRcdHZhciBpbmRleCA9IGRpciA+IDAgPyAwIDogbGVuZ3RoIC0gMTtcblx0XHRcdGZvciAoOyBpbmRleCA+PSAwICYmIGluZGV4IDwgbGVuZ3RoOyBpbmRleCArPSBkaXIpIHtcblx0XHRcdFx0aWYgKHByZWRpY2F0ZShhcnJheVtpbmRleF0sIGluZGV4LCBhcnJheSkpIHJldHVybiBpbmRleDtcblx0XHRcdH1cblx0XHRcdHJldHVybiAtMTtcblx0XHR9O1xuXHR9XG5cblx0Ly8gUmV0dXJucyB0aGUgZmlyc3QgaW5kZXggb24gYW4gYXJyYXktbGlrZSB0aGF0IHBhc3NlcyBhIHByZWRpY2F0ZSB0ZXN0XG5cdF8uZmluZEluZGV4ID0gY3JlYXRlSW5kZXhGaW5kZXIoMSk7XG5cblx0Xy5maW5kTGFzdEluZGV4ID0gY3JlYXRlSW5kZXhGaW5kZXIoLTEpO1xuXG5cdC8vIFVzZSBhIGNvbXBhcmF0b3IgZnVuY3Rpb24gdG8gZmlndXJlIG91dCB0aGUgc21hbGxlc3QgaW5kZXggYXQgd2hpY2hcblx0Ly8gYW4gb2JqZWN0IHNob3VsZCBiZSBpbnNlcnRlZCBzbyBhcyB0byBtYWludGFpbiBvcmRlci4gVXNlcyBiaW5hcnkgc2VhcmNoLlxuXHRfLnNvcnRlZEluZGV4ID0gZnVuY3Rpb24gKGFycmF5LCBvYmosIGl0ZXJhdGVlLCBjb250ZXh0KSB7XG5cdFx0aXRlcmF0ZWUgPSBjYihpdGVyYXRlZSwgY29udGV4dCwgMSk7XG5cdFx0dmFyIHZhbHVlID0gaXRlcmF0ZWUob2JqKTtcblx0XHR2YXIgbG93ID0gMCwgaGlnaCA9IGFycmF5Lmxlbmd0aDtcblx0XHR3aGlsZSAobG93IDwgaGlnaCkge1xuXHRcdFx0dmFyIG1pZCA9IE1hdGguZmxvb3IoKGxvdyArIGhpZ2gpIC8gMik7XG5cdFx0XHRpZiAoaXRlcmF0ZWUoYXJyYXlbbWlkXSkgPCB2YWx1ZSkgbG93ID0gbWlkICsgMTsgZWxzZSBoaWdoID0gbWlkO1xuXHRcdH1cblx0XHRyZXR1cm4gbG93O1xuXHR9O1xuXG5cdC8vIEdlbmVyYXRlIGFuIGludGVnZXIgQXJyYXkgY29udGFpbmluZyBhbiBhcml0aG1ldGljIHByb2dyZXNzaW9uLiBBIHBvcnQgb2Zcblx0Ly8gdGhlIG5hdGl2ZSBQeXRob24gYHJhbmdlKClgIGZ1bmN0aW9uLiBTZWVcblx0Ly8gW3RoZSBQeXRob24gZG9jdW1lbnRhdGlvbl0oaHR0cDovL2RvY3MucHl0aG9uLm9yZy9saWJyYXJ5L2Z1bmN0aW9ucy5odG1sI3JhbmdlKS5cblx0Xy5yYW5nZSA9IGZ1bmN0aW9uIChzdGFydCwgc3RvcCwgc3RlcCkge1xuXHRcdGlmIChhcmd1bWVudHMubGVuZ3RoIDw9IDEpIHtcblx0XHRcdHN0b3AgPSBzdGFydCB8fCAwO1xuXHRcdFx0c3RhcnQgPSAwO1xuXHRcdH1cblx0XHRzdGVwID0gc3RlcCB8fCAxO1xuXG5cdFx0dmFyIGxlbmd0aCA9IE1hdGgubWF4KE1hdGguY2VpbCgoc3RvcCAtIHN0YXJ0KSAvIHN0ZXApLCAwKTtcblx0XHR2YXIgcmFuZ2UgPSBBcnJheShsZW5ndGgpO1xuXG5cdFx0Zm9yICh2YXIgaWR4ID0gMDsgaWR4IDwgbGVuZ3RoOyBpZHgrKyAsIHN0YXJ0ICs9IHN0ZXApIHtcblx0XHRcdHJhbmdlW2lkeF0gPSBzdGFydDtcblx0XHR9XG5cblx0XHRyZXR1cm4gcmFuZ2U7XG5cdH07XG5cblx0Ly8gRnVuY3Rpb24gKGFoZW0pIEZ1bmN0aW9uc1xuXHQvLyAtLS0tLS0tLS0tLS0tLS0tLS1cblxuXHQvLyBEZXRlcm1pbmVzIHdoZXRoZXIgdG8gZXhlY3V0ZSBhIGZ1bmN0aW9uIGFzIGEgY29uc3RydWN0b3Jcblx0Ly8gb3IgYSBub3JtYWwgZnVuY3Rpb24gd2l0aCB0aGUgcHJvdmlkZWQgYXJndW1lbnRzXG5cdHZhciBleGVjdXRlQm91bmQgPSBmdW5jdGlvbiAoc291cmNlRnVuYywgYm91bmRGdW5jLCBjb250ZXh0LCBjYWxsaW5nQ29udGV4dCwgYXJncykge1xuXHRcdGlmICghKGNhbGxpbmdDb250ZXh0IGluc3RhbmNlb2YgYm91bmRGdW5jKSkgcmV0dXJuIHNvdXJjZUZ1bmMuYXBwbHkoY29udGV4dCwgYXJncyk7XG5cdFx0dmFyIHNlbGYgPSBiYXNlQ3JlYXRlKHNvdXJjZUZ1bmMucHJvdG90eXBlKTtcblx0XHR2YXIgcmVzdWx0ID0gc291cmNlRnVuYy5hcHBseShzZWxmLCBhcmdzKTtcblx0XHRpZiAoXy5pc09iamVjdChyZXN1bHQpKSByZXR1cm4gcmVzdWx0O1xuXHRcdHJldHVybiBzZWxmO1xuXHR9O1xuXG5cdC8vIENyZWF0ZSBhIGZ1bmN0aW9uIGJvdW5kIHRvIGEgZ2l2ZW4gb2JqZWN0IChhc3NpZ25pbmcgYHRoaXNgLCBhbmQgYXJndW1lbnRzLFxuXHQvLyBvcHRpb25hbGx5KS4gRGVsZWdhdGVzIHRvICoqRUNNQVNjcmlwdCA1KioncyBuYXRpdmUgYEZ1bmN0aW9uLmJpbmRgIGlmXG5cdC8vIGF2YWlsYWJsZS5cblx0Xy5iaW5kID0gZnVuY3Rpb24gKGZ1bmMsIGNvbnRleHQpIHtcblx0XHRpZiAobmF0aXZlQmluZCAmJiBmdW5jLmJpbmQgPT09IG5hdGl2ZUJpbmQpIHJldHVybiBuYXRpdmVCaW5kLmFwcGx5KGZ1bmMsIHNsaWNlLmNhbGwoYXJndW1lbnRzLCAxKSk7XG5cdFx0aWYgKCFfLmlzRnVuY3Rpb24oZnVuYykpIHRocm93IG5ldyBUeXBlRXJyb3IoJ0JpbmQgbXVzdCBiZSBjYWxsZWQgb24gYSBmdW5jdGlvbicpO1xuXHRcdHZhciBhcmdzID0gc2xpY2UuY2FsbChhcmd1bWVudHMsIDIpO1xuXHRcdHZhciBib3VuZCA9IGZ1bmN0aW9uICgpIHtcblx0XHRcdHJldHVybiBleGVjdXRlQm91bmQoZnVuYywgYm91bmQsIGNvbnRleHQsIHRoaXMsIGFyZ3MuY29uY2F0KHNsaWNlLmNhbGwoYXJndW1lbnRzKSkpO1xuXHRcdH07XG5cdFx0cmV0dXJuIGJvdW5kO1xuXHR9O1xuXG5cdC8vIFBhcnRpYWxseSBhcHBseSBhIGZ1bmN0aW9uIGJ5IGNyZWF0aW5nIGEgdmVyc2lvbiB0aGF0IGhhcyBoYWQgc29tZSBvZiBpdHNcblx0Ly8gYXJndW1lbnRzIHByZS1maWxsZWQsIHdpdGhvdXQgY2hhbmdpbmcgaXRzIGR5bmFtaWMgYHRoaXNgIGNvbnRleHQuIF8gYWN0c1xuXHQvLyBhcyBhIHBsYWNlaG9sZGVyLCBhbGxvd2luZyBhbnkgY29tYmluYXRpb24gb2YgYXJndW1lbnRzIHRvIGJlIHByZS1maWxsZWQuXG5cdF8ucGFydGlhbCA9IGZ1bmN0aW9uIChmdW5jKSB7XG5cdFx0dmFyIGJvdW5kQXJncyA9IHNsaWNlLmNhbGwoYXJndW1lbnRzLCAxKTtcblx0XHR2YXIgYm91bmQgPSBmdW5jdGlvbiAoKSB7XG5cdFx0XHR2YXIgcG9zaXRpb24gPSAwLCBsZW5ndGggPSBib3VuZEFyZ3MubGVuZ3RoO1xuXHRcdFx0dmFyIGFyZ3MgPSBBcnJheShsZW5ndGgpO1xuXHRcdFx0Zm9yICh2YXIgaSA9IDA7IGkgPCBsZW5ndGg7IGkrKykge1xuXHRcdFx0XHRhcmdzW2ldID0gYm91bmRBcmdzW2ldID09PSBfID8gYXJndW1lbnRzW3Bvc2l0aW9uKytdIDogYm91bmRBcmdzW2ldO1xuXHRcdFx0fVxuXHRcdFx0d2hpbGUgKHBvc2l0aW9uIDwgYXJndW1lbnRzLmxlbmd0aCkgYXJncy5wdXNoKGFyZ3VtZW50c1twb3NpdGlvbisrXSk7XG5cdFx0XHRyZXR1cm4gZXhlY3V0ZUJvdW5kKGZ1bmMsIGJvdW5kLCB0aGlzLCB0aGlzLCBhcmdzKTtcblx0XHR9O1xuXHRcdHJldHVybiBib3VuZDtcblx0fTtcblxuXHQvLyBCaW5kIGEgbnVtYmVyIG9mIGFuIG9iamVjdCdzIG1ldGhvZHMgdG8gdGhhdCBvYmplY3QuIFJlbWFpbmluZyBhcmd1bWVudHNcblx0Ly8gYXJlIHRoZSBtZXRob2QgbmFtZXMgdG8gYmUgYm91bmQuIFVzZWZ1bCBmb3IgZW5zdXJpbmcgdGhhdCBhbGwgY2FsbGJhY2tzXG5cdC8vIGRlZmluZWQgb24gYW4gb2JqZWN0IGJlbG9uZyB0byBpdC5cblx0Xy5iaW5kQWxsID0gZnVuY3Rpb24gKG9iaikge1xuXHRcdHZhciBpLCBsZW5ndGggPSBhcmd1bWVudHMubGVuZ3RoLCBrZXk7XG5cdFx0aWYgKGxlbmd0aCA8PSAxKSB0aHJvdyBuZXcgRXJyb3IoJ2JpbmRBbGwgbXVzdCBiZSBwYXNzZWQgZnVuY3Rpb24gbmFtZXMnKTtcblx0XHRmb3IgKGkgPSAxOyBpIDwgbGVuZ3RoOyBpKyspIHtcblx0XHRcdGtleSA9IGFyZ3VtZW50c1tpXTtcblx0XHRcdG9ialtrZXldID0gXy5iaW5kKG9ialtrZXldLCBvYmopO1xuXHRcdH1cblx0XHRyZXR1cm4gb2JqO1xuXHR9O1xuXG5cdC8vIE1lbW9pemUgYW4gZXhwZW5zaXZlIGZ1bmN0aW9uIGJ5IHN0b3JpbmcgaXRzIHJlc3VsdHMuXG5cdF8ubWVtb2l6ZSA9IGZ1bmN0aW9uIChmdW5jLCBoYXNoZXIpIHtcblx0XHR2YXIgbWVtb2l6ZSA9IGZ1bmN0aW9uIChrZXkpIHtcblx0XHRcdHZhciBjYWNoZSA9IG1lbW9pemUuY2FjaGU7XG5cdFx0XHR2YXIgYWRkcmVzcyA9ICcnICsgKGhhc2hlciA/IGhhc2hlci5hcHBseSh0aGlzLCBhcmd1bWVudHMpIDoga2V5KTtcblx0XHRcdGlmICghXy5oYXMoY2FjaGUsIGFkZHJlc3MpKSBjYWNoZVthZGRyZXNzXSA9IGZ1bmMuYXBwbHkodGhpcywgYXJndW1lbnRzKTtcblx0XHRcdHJldHVybiBjYWNoZVthZGRyZXNzXTtcblx0XHR9O1xuXHRcdG1lbW9pemUuY2FjaGUgPSB7fTtcblx0XHRyZXR1cm4gbWVtb2l6ZTtcblx0fTtcblxuXHQvLyBEZWxheXMgYSBmdW5jdGlvbiBmb3IgdGhlIGdpdmVuIG51bWJlciBvZiBtaWxsaXNlY29uZHMsIGFuZCB0aGVuIGNhbGxzXG5cdC8vIGl0IHdpdGggdGhlIGFyZ3VtZW50cyBzdXBwbGllZC5cblx0Xy5kZWxheSA9IGZ1bmN0aW9uIChmdW5jLCB3YWl0KSB7XG5cdFx0dmFyIGFyZ3MgPSBzbGljZS5jYWxsKGFyZ3VtZW50cywgMik7XG5cdFx0cmV0dXJuIHNldFRpbWVvdXQoZnVuY3Rpb24gKCkge1xuXHRcdFx0cmV0dXJuIGZ1bmMuYXBwbHkobnVsbCwgYXJncyk7XG5cdFx0fSwgd2FpdCk7XG5cdH07XG5cblx0Ly8gRGVmZXJzIGEgZnVuY3Rpb24sIHNjaGVkdWxpbmcgaXQgdG8gcnVuIGFmdGVyIHRoZSBjdXJyZW50IGNhbGwgc3RhY2sgaGFzXG5cdC8vIGNsZWFyZWQuXG5cdF8uZGVmZXIgPSBfLnBhcnRpYWwoXy5kZWxheSwgXywgMSk7XG5cblx0Ly8gUmV0dXJucyBhIGZ1bmN0aW9uLCB0aGF0LCB3aGVuIGludm9rZWQsIHdpbGwgb25seSBiZSB0cmlnZ2VyZWQgYXQgbW9zdCBvbmNlXG5cdC8vIGR1cmluZyBhIGdpdmVuIHdpbmRvdyBvZiB0aW1lLiBOb3JtYWxseSwgdGhlIHRocm90dGxlZCBmdW5jdGlvbiB3aWxsIHJ1blxuXHQvLyBhcyBtdWNoIGFzIGl0IGNhbiwgd2l0aG91dCBldmVyIGdvaW5nIG1vcmUgdGhhbiBvbmNlIHBlciBgd2FpdGAgZHVyYXRpb247XG5cdC8vIGJ1dCBpZiB5b3UnZCBsaWtlIHRvIGRpc2FibGUgdGhlIGV4ZWN1dGlvbiBvbiB0aGUgbGVhZGluZyBlZGdlLCBwYXNzXG5cdC8vIGB7bGVhZGluZzogZmFsc2V9YC4gVG8gZGlzYWJsZSBleGVjdXRpb24gb24gdGhlIHRyYWlsaW5nIGVkZ2UsIGRpdHRvLlxuXHRfLnRocm90dGxlID0gZnVuY3Rpb24gKGZ1bmMsIHdhaXQsIG9wdGlvbnMpIHtcblx0XHR2YXIgY29udGV4dCwgYXJncywgcmVzdWx0O1xuXHRcdHZhciB0aW1lb3V0ID0gbnVsbDtcblx0XHR2YXIgcHJldmlvdXMgPSAwO1xuXHRcdGlmICghb3B0aW9ucykgb3B0aW9ucyA9IHt9O1xuXHRcdHZhciBsYXRlciA9IGZ1bmN0aW9uICgpIHtcblx0XHRcdHByZXZpb3VzID0gb3B0aW9ucy5sZWFkaW5nID09PSBmYWxzZSA/IDAgOiBfLm5vdygpO1xuXHRcdFx0dGltZW91dCA9IG51bGw7XG5cdFx0XHRyZXN1bHQgPSBmdW5jLmFwcGx5KGNvbnRleHQsIGFyZ3MpO1xuXHRcdFx0aWYgKCF0aW1lb3V0KSBjb250ZXh0ID0gYXJncyA9IG51bGw7XG5cdFx0fTtcblx0XHRyZXR1cm4gZnVuY3Rpb24gKCkge1xuXHRcdFx0dmFyIG5vdyA9IF8ubm93KCk7XG5cdFx0XHRpZiAoIXByZXZpb3VzICYmIG9wdGlvbnMubGVhZGluZyA9PT0gZmFsc2UpIHByZXZpb3VzID0gbm93O1xuXHRcdFx0dmFyIHJlbWFpbmluZyA9IHdhaXQgLSAobm93IC0gcHJldmlvdXMpO1xuXHRcdFx0Y29udGV4dCA9IHRoaXM7XG5cdFx0XHRhcmdzID0gYXJndW1lbnRzO1xuXHRcdFx0aWYgKHJlbWFpbmluZyA8PSAwIHx8IHJlbWFpbmluZyA+IHdhaXQpIHtcblx0XHRcdFx0aWYgKHRpbWVvdXQpIHtcblx0XHRcdFx0XHRjbGVhclRpbWVvdXQodGltZW91dCk7XG5cdFx0XHRcdFx0dGltZW91dCA9IG51bGw7XG5cdFx0XHRcdH1cblx0XHRcdFx0cHJldmlvdXMgPSBub3c7XG5cdFx0XHRcdHJlc3VsdCA9IGZ1bmMuYXBwbHkoY29udGV4dCwgYXJncyk7XG5cdFx0XHRcdGlmICghdGltZW91dCkgY29udGV4dCA9IGFyZ3MgPSBudWxsO1xuXHRcdFx0fSBlbHNlIGlmICghdGltZW91dCAmJiBvcHRpb25zLnRyYWlsaW5nICE9PSBmYWxzZSkge1xuXHRcdFx0XHR0aW1lb3V0ID0gc2V0VGltZW91dChsYXRlciwgcmVtYWluaW5nKTtcblx0XHRcdH1cblx0XHRcdHJldHVybiByZXN1bHQ7XG5cdFx0fTtcblx0fTtcblxuXHQvLyBSZXR1cm5zIGEgZnVuY3Rpb24sIHRoYXQsIGFzIGxvbmcgYXMgaXQgY29udGludWVzIHRvIGJlIGludm9rZWQsIHdpbGwgbm90XG5cdC8vIGJlIHRyaWdnZXJlZC4gVGhlIGZ1bmN0aW9uIHdpbGwgYmUgY2FsbGVkIGFmdGVyIGl0IHN0b3BzIGJlaW5nIGNhbGxlZCBmb3Jcblx0Ly8gTiBtaWxsaXNlY29uZHMuIElmIGBpbW1lZGlhdGVgIGlzIHBhc3NlZCwgdHJpZ2dlciB0aGUgZnVuY3Rpb24gb24gdGhlXG5cdC8vIGxlYWRpbmcgZWRnZSwgaW5zdGVhZCBvZiB0aGUgdHJhaWxpbmcuXG5cdF8uZGVib3VuY2UgPSBmdW5jdGlvbiAoZnVuYywgd2FpdCwgaW1tZWRpYXRlKSB7XG5cdFx0dmFyIHRpbWVvdXQsIGFyZ3MsIGNvbnRleHQsIHRpbWVzdGFtcCwgcmVzdWx0O1xuXG5cdFx0dmFyIGxhdGVyID0gZnVuY3Rpb24gKCkge1xuXHRcdFx0dmFyIGxhc3QgPSBfLm5vdygpIC0gdGltZXN0YW1wO1xuXG5cdFx0XHRpZiAobGFzdCA8IHdhaXQgJiYgbGFzdCA+PSAwKSB7XG5cdFx0XHRcdHRpbWVvdXQgPSBzZXRUaW1lb3V0KGxhdGVyLCB3YWl0IC0gbGFzdCk7XG5cdFx0XHR9IGVsc2Uge1xuXHRcdFx0XHR0aW1lb3V0ID0gbnVsbDtcblx0XHRcdFx0aWYgKCFpbW1lZGlhdGUpIHtcblx0XHRcdFx0XHRyZXN1bHQgPSBmdW5jLmFwcGx5KGNvbnRleHQsIGFyZ3MpO1xuXHRcdFx0XHRcdGlmICghdGltZW91dCkgY29udGV4dCA9IGFyZ3MgPSBudWxsO1xuXHRcdFx0XHR9XG5cdFx0XHR9XG5cdFx0fTtcblxuXHRcdHJldHVybiBmdW5jdGlvbiAoKSB7XG5cdFx0XHRjb250ZXh0ID0gdGhpcztcblx0XHRcdGFyZ3MgPSBhcmd1bWVudHM7XG5cdFx0XHR0aW1lc3RhbXAgPSBfLm5vdygpO1xuXHRcdFx0dmFyIGNhbGxOb3cgPSBpbW1lZGlhdGUgJiYgIXRpbWVvdXQ7XG5cdFx0XHRpZiAoIXRpbWVvdXQpIHRpbWVvdXQgPSBzZXRUaW1lb3V0KGxhdGVyLCB3YWl0KTtcblx0XHRcdGlmIChjYWxsTm93KSB7XG5cdFx0XHRcdHJlc3VsdCA9IGZ1bmMuYXBwbHkoY29udGV4dCwgYXJncyk7XG5cdFx0XHRcdGNvbnRleHQgPSBhcmdzID0gbnVsbDtcblx0XHRcdH1cblxuXHRcdFx0cmV0dXJuIHJlc3VsdDtcblx0XHR9O1xuXHR9O1xuXG5cdC8vIFJldHVybnMgdGhlIGZpcnN0IGZ1bmN0aW9uIHBhc3NlZCBhcyBhbiBhcmd1bWVudCB0byB0aGUgc2Vjb25kLFxuXHQvLyBhbGxvd2luZyB5b3UgdG8gYWRqdXN0IGFyZ3VtZW50cywgcnVuIGNvZGUgYmVmb3JlIGFuZCBhZnRlciwgYW5kXG5cdC8vIGNvbmRpdGlvbmFsbHkgZXhlY3V0ZSB0aGUgb3JpZ2luYWwgZnVuY3Rpb24uXG5cdF8ud3JhcCA9IGZ1bmN0aW9uIChmdW5jLCB3cmFwcGVyKSB7XG5cdFx0cmV0dXJuIF8ucGFydGlhbCh3cmFwcGVyLCBmdW5jKTtcblx0fTtcblxuXHQvLyBSZXR1cm5zIGEgbmVnYXRlZCB2ZXJzaW9uIG9mIHRoZSBwYXNzZWQtaW4gcHJlZGljYXRlLlxuXHRfLm5lZ2F0ZSA9IGZ1bmN0aW9uIChwcmVkaWNhdGUpIHtcblx0XHRyZXR1cm4gZnVuY3Rpb24gKCkge1xuXHRcdFx0cmV0dXJuICFwcmVkaWNhdGUuYXBwbHkodGhpcywgYXJndW1lbnRzKTtcblx0XHR9O1xuXHR9O1xuXG5cdC8vIFJldHVybnMgYSBmdW5jdGlvbiB0aGF0IGlzIHRoZSBjb21wb3NpdGlvbiBvZiBhIGxpc3Qgb2YgZnVuY3Rpb25zLCBlYWNoXG5cdC8vIGNvbnN1bWluZyB0aGUgcmV0dXJuIHZhbHVlIG9mIHRoZSBmdW5jdGlvbiB0aGF0IGZvbGxvd3MuXG5cdF8uY29tcG9zZSA9IGZ1bmN0aW9uICgpIHtcblx0XHR2YXIgYXJncyA9IGFyZ3VtZW50cztcblx0XHR2YXIgc3RhcnQgPSBhcmdzLmxlbmd0aCAtIDE7XG5cdFx0cmV0dXJuIGZ1bmN0aW9uICgpIHtcblx0XHRcdHZhciBpID0gc3RhcnQ7XG5cdFx0XHR2YXIgcmVzdWx0ID0gYXJnc1tzdGFydF0uYXBwbHkodGhpcywgYXJndW1lbnRzKTtcblx0XHRcdHdoaWxlIChpLS0pIHJlc3VsdCA9IGFyZ3NbaV0uY2FsbCh0aGlzLCByZXN1bHQpO1xuXHRcdFx0cmV0dXJuIHJlc3VsdDtcblx0XHR9O1xuXHR9O1xuXG5cdC8vIFJldHVybnMgYSBmdW5jdGlvbiB0aGF0IHdpbGwgb25seSBiZSBleGVjdXRlZCBvbiBhbmQgYWZ0ZXIgdGhlIE50aCBjYWxsLlxuXHRfLmFmdGVyID0gZnVuY3Rpb24gKHRpbWVzLCBmdW5jKSB7XG5cdFx0cmV0dXJuIGZ1bmN0aW9uICgpIHtcblx0XHRcdGlmICgtLXRpbWVzIDwgMSkge1xuXHRcdFx0XHRyZXR1cm4gZnVuYy5hcHBseSh0aGlzLCBhcmd1bWVudHMpO1xuXHRcdFx0fVxuXHRcdH07XG5cdH07XG5cblx0Ly8gUmV0dXJucyBhIGZ1bmN0aW9uIHRoYXQgd2lsbCBvbmx5IGJlIGV4ZWN1dGVkIHVwIHRvIChidXQgbm90IGluY2x1ZGluZykgdGhlIE50aCBjYWxsLlxuXHRfLmJlZm9yZSA9IGZ1bmN0aW9uICh0aW1lcywgZnVuYykge1xuXHRcdHZhciBtZW1vO1xuXHRcdHJldHVybiBmdW5jdGlvbiAoKSB7XG5cdFx0XHRpZiAoLS10aW1lcyA+IDApIHtcblx0XHRcdFx0bWVtbyA9IGZ1bmMuYXBwbHkodGhpcywgYXJndW1lbnRzKTtcblx0XHRcdH1cblx0XHRcdGlmICh0aW1lcyA8PSAxKSBmdW5jID0gbnVsbDtcblx0XHRcdHJldHVybiBtZW1vO1xuXHRcdH07XG5cdH07XG5cblx0Ly8gUmV0dXJucyBhIGZ1bmN0aW9uIHRoYXQgd2lsbCBiZSBleGVjdXRlZCBhdCBtb3N0IG9uZSB0aW1lLCBubyBtYXR0ZXIgaG93XG5cdC8vIG9mdGVuIHlvdSBjYWxsIGl0LiBVc2VmdWwgZm9yIGxhenkgaW5pdGlhbGl6YXRpb24uXG5cdF8ub25jZSA9IF8ucGFydGlhbChfLmJlZm9yZSwgMik7XG5cblx0Ly8gT2JqZWN0IEZ1bmN0aW9uc1xuXHQvLyAtLS0tLS0tLS0tLS0tLS0tXG5cblx0Ly8gS2V5cyBpbiBJRSA8IDkgdGhhdCB3b24ndCBiZSBpdGVyYXRlZCBieSBgZm9yIGtleSBpbiAuLi5gIGFuZCB0aHVzIG1pc3NlZC5cblx0dmFyIGhhc0VudW1CdWcgPSAheyB0b1N0cmluZzogbnVsbCB9LnByb3BlcnR5SXNFbnVtZXJhYmxlKCd0b1N0cmluZycpO1xuXHR2YXIgbm9uRW51bWVyYWJsZVByb3BzID0gWyd2YWx1ZU9mJywgJ2lzUHJvdG90eXBlT2YnLCAndG9TdHJpbmcnLFxuXHRcdCdwcm9wZXJ0eUlzRW51bWVyYWJsZScsICdoYXNPd25Qcm9wZXJ0eScsICd0b0xvY2FsZVN0cmluZyddO1xuXG5cdGZ1bmN0aW9uIGNvbGxlY3ROb25FbnVtUHJvcHMob2JqLCBrZXlzKSB7XG5cdFx0dmFyIG5vbkVudW1JZHggPSBub25FbnVtZXJhYmxlUHJvcHMubGVuZ3RoO1xuXHRcdHZhciBjb25zdHJ1Y3RvciA9IG9iai5jb25zdHJ1Y3Rvcjtcblx0XHR2YXIgcHJvdG8gPSAoXy5pc0Z1bmN0aW9uKGNvbnN0cnVjdG9yKSAmJiBjb25zdHJ1Y3Rvci5wcm90b3R5cGUpIHx8IE9ialByb3RvO1xuXG5cdFx0Ly8gQ29uc3RydWN0b3IgaXMgYSBzcGVjaWFsIGNhc2UuXG5cdFx0dmFyIHByb3AgPSAnY29uc3RydWN0b3InO1xuXHRcdGlmIChfLmhhcyhvYmosIHByb3ApICYmICFfLmNvbnRhaW5zKGtleXMsIHByb3ApKSBrZXlzLnB1c2gocHJvcCk7XG5cblx0XHR3aGlsZSAobm9uRW51bUlkeC0tKSB7XG5cdFx0XHRwcm9wID0gbm9uRW51bWVyYWJsZVByb3BzW25vbkVudW1JZHhdO1xuXHRcdFx0aWYgKHByb3AgaW4gb2JqICYmIG9ialtwcm9wXSAhPT0gcHJvdG9bcHJvcF0gJiYgIV8uY29udGFpbnMoa2V5cywgcHJvcCkpIHtcblx0XHRcdFx0a2V5cy5wdXNoKHByb3ApO1xuXHRcdFx0fVxuXHRcdH1cblx0fVxuXG5cdC8vIFJldHJpZXZlIHRoZSBuYW1lcyBvZiBhbiBvYmplY3QncyBvd24gcHJvcGVydGllcy5cblx0Ly8gRGVsZWdhdGVzIHRvICoqRUNNQVNjcmlwdCA1KioncyBuYXRpdmUgYE9iamVjdC5rZXlzYFxuXHRfLmtleXMgPSBmdW5jdGlvbiAob2JqKSB7XG5cdFx0aWYgKCFfLmlzT2JqZWN0KG9iaikpIHJldHVybiBbXTtcblx0XHRpZiAobmF0aXZlS2V5cykgcmV0dXJuIG5hdGl2ZUtleXMob2JqKTtcblx0XHR2YXIga2V5cyA9IFtdO1xuXHRcdGZvciAodmFyIGtleSBpbiBvYmopIGlmIChfLmhhcyhvYmosIGtleSkpIGtleXMucHVzaChrZXkpO1xuXHRcdC8vIEFoZW0sIElFIDwgOS5cblx0XHRpZiAoaGFzRW51bUJ1ZykgY29sbGVjdE5vbkVudW1Qcm9wcyhvYmosIGtleXMpO1xuXHRcdHJldHVybiBrZXlzO1xuXHR9O1xuXG5cdC8vIFJldHJpZXZlIGFsbCB0aGUgcHJvcGVydHkgbmFtZXMgb2YgYW4gb2JqZWN0LlxuXHRfLmFsbEtleXMgPSBmdW5jdGlvbiAob2JqKSB7XG5cdFx0aWYgKCFfLmlzT2JqZWN0KG9iaikpIHJldHVybiBbXTtcblx0XHR2YXIga2V5cyA9IFtdO1xuXHRcdGZvciAodmFyIGtleSBpbiBvYmopIGtleXMucHVzaChrZXkpO1xuXHRcdC8vIEFoZW0sIElFIDwgOS5cblx0XHRpZiAoaGFzRW51bUJ1ZykgY29sbGVjdE5vbkVudW1Qcm9wcyhvYmosIGtleXMpO1xuXHRcdHJldHVybiBrZXlzO1xuXHR9O1xuXG5cdC8vIFJldHJpZXZlIHRoZSB2YWx1ZXMgb2YgYW4gb2JqZWN0J3MgcHJvcGVydGllcy5cblx0Xy52YWx1ZXMgPSBmdW5jdGlvbiAob2JqKSB7XG5cdFx0dmFyIGtleXMgPSBfLmtleXMob2JqKTtcblx0XHR2YXIgbGVuZ3RoID0ga2V5cy5sZW5ndGg7XG5cdFx0dmFyIHZhbHVlcyA9IEFycmF5KGxlbmd0aCk7XG5cdFx0Zm9yICh2YXIgaSA9IDA7IGkgPCBsZW5ndGg7IGkrKykge1xuXHRcdFx0dmFsdWVzW2ldID0gb2JqW2tleXNbaV1dO1xuXHRcdH1cblx0XHRyZXR1cm4gdmFsdWVzO1xuXHR9O1xuXG5cdC8vIFJldHVybnMgdGhlIHJlc3VsdHMgb2YgYXBwbHlpbmcgdGhlIGl0ZXJhdGVlIHRvIGVhY2ggZWxlbWVudCBvZiB0aGUgb2JqZWN0XG5cdC8vIEluIGNvbnRyYXN0IHRvIF8ubWFwIGl0IHJldHVybnMgYW4gb2JqZWN0XG5cdF8ubWFwT2JqZWN0ID0gZnVuY3Rpb24gKG9iaiwgaXRlcmF0ZWUsIGNvbnRleHQpIHtcblx0XHRpdGVyYXRlZSA9IGNiKGl0ZXJhdGVlLCBjb250ZXh0KTtcblx0XHR2YXIga2V5cyA9IF8ua2V5cyhvYmopLFxuXHRcdFx0bGVuZ3RoID0ga2V5cy5sZW5ndGgsXG5cdFx0XHRyZXN1bHRzID0ge30sXG5cdFx0XHRjdXJyZW50S2V5O1xuXHRcdGZvciAodmFyIGluZGV4ID0gMDsgaW5kZXggPCBsZW5ndGg7IGluZGV4KyspIHtcblx0XHRcdGN1cnJlbnRLZXkgPSBrZXlzW2luZGV4XTtcblx0XHRcdHJlc3VsdHNbY3VycmVudEtleV0gPSBpdGVyYXRlZShvYmpbY3VycmVudEtleV0sIGN1cnJlbnRLZXksIG9iaik7XG5cdFx0fVxuXHRcdHJldHVybiByZXN1bHRzO1xuXHR9O1xuXG5cdC8vIENvbnZlcnQgYW4gb2JqZWN0IGludG8gYSBsaXN0IG9mIGBba2V5LCB2YWx1ZV1gIHBhaXJzLlxuXHRfLnBhaXJzID0gZnVuY3Rpb24gKG9iaikge1xuXHRcdHZhciBrZXlzID0gXy5rZXlzKG9iaik7XG5cdFx0dmFyIGxlbmd0aCA9IGtleXMubGVuZ3RoO1xuXHRcdHZhciBwYWlycyA9IEFycmF5KGxlbmd0aCk7XG5cdFx0Zm9yICh2YXIgaSA9IDA7IGkgPCBsZW5ndGg7IGkrKykge1xuXHRcdFx0cGFpcnNbaV0gPSBba2V5c1tpXSwgb2JqW2tleXNbaV1dXTtcblx0XHR9XG5cdFx0cmV0dXJuIHBhaXJzO1xuXHR9O1xuXG5cdC8vIEludmVydCB0aGUga2V5cyBhbmQgdmFsdWVzIG9mIGFuIG9iamVjdC4gVGhlIHZhbHVlcyBtdXN0IGJlIHNlcmlhbGl6YWJsZS5cblx0Xy5pbnZlcnQgPSBmdW5jdGlvbiAob2JqKSB7XG5cdFx0dmFyIHJlc3VsdCA9IHt9O1xuXHRcdHZhciBrZXlzID0gXy5rZXlzKG9iaik7XG5cdFx0Zm9yICh2YXIgaSA9IDAsIGxlbmd0aCA9IGtleXMubGVuZ3RoOyBpIDwgbGVuZ3RoOyBpKyspIHtcblx0XHRcdHJlc3VsdFtvYmpba2V5c1tpXV1dID0ga2V5c1tpXTtcblx0XHR9XG5cdFx0cmV0dXJuIHJlc3VsdDtcblx0fTtcblxuXHQvLyBSZXR1cm4gYSBzb3J0ZWQgbGlzdCBvZiB0aGUgZnVuY3Rpb24gbmFtZXMgYXZhaWxhYmxlIG9uIHRoZSBvYmplY3QuXG5cdC8vIEFsaWFzZWQgYXMgYG1ldGhvZHNgXG5cdF8uZnVuY3Rpb25zID0gXy5tZXRob2RzID0gZnVuY3Rpb24gKG9iaikge1xuXHRcdHZhciBuYW1lcyA9IFtdO1xuXHRcdGZvciAodmFyIGtleSBpbiBvYmopIHtcblx0XHRcdGlmIChfLmlzRnVuY3Rpb24ob2JqW2tleV0pKSBuYW1lcy5wdXNoKGtleSk7XG5cdFx0fVxuXHRcdHJldHVybiBuYW1lcy5zb3J0KCk7XG5cdH07XG5cblx0Ly8gRXh0ZW5kIGEgZ2l2ZW4gb2JqZWN0IHdpdGggYWxsIHRoZSBwcm9wZXJ0aWVzIGluIHBhc3NlZC1pbiBvYmplY3QocykuXG5cdF8uZXh0ZW5kID0gY3JlYXRlQXNzaWduZXIoXy5hbGxLZXlzKTtcblxuXHQvLyBBc3NpZ25zIGEgZ2l2ZW4gb2JqZWN0IHdpdGggYWxsIHRoZSBvd24gcHJvcGVydGllcyBpbiB0aGUgcGFzc2VkLWluIG9iamVjdChzKVxuXHQvLyAoaHR0cHM6Ly9kZXZlbG9wZXIubW96aWxsYS5vcmcvZG9jcy9XZWIvSmF2YVNjcmlwdC9SZWZlcmVuY2UvR2xvYmFsX09iamVjdHMvT2JqZWN0L2Fzc2lnbilcblx0Xy5leHRlbmRPd24gPSBfLmFzc2lnbiA9IGNyZWF0ZUFzc2lnbmVyKF8ua2V5cyk7XG5cblx0Ly8gUmV0dXJucyB0aGUgZmlyc3Qga2V5IG9uIGFuIG9iamVjdCB0aGF0IHBhc3NlcyBhIHByZWRpY2F0ZSB0ZXN0XG5cdF8uZmluZEtleSA9IGZ1bmN0aW9uIChvYmosIHByZWRpY2F0ZSwgY29udGV4dCkge1xuXHRcdHByZWRpY2F0ZSA9IGNiKHByZWRpY2F0ZSwgY29udGV4dCk7XG5cdFx0dmFyIGtleXMgPSBfLmtleXMob2JqKSwga2V5O1xuXHRcdGZvciAodmFyIGkgPSAwLCBsZW5ndGggPSBrZXlzLmxlbmd0aDsgaSA8IGxlbmd0aDsgaSsrKSB7XG5cdFx0XHRrZXkgPSBrZXlzW2ldO1xuXHRcdFx0aWYgKHByZWRpY2F0ZShvYmpba2V5XSwga2V5LCBvYmopKSByZXR1cm4ga2V5O1xuXHRcdH1cblx0fTtcblxuXHQvLyBSZXR1cm4gYSBjb3B5IG9mIHRoZSBvYmplY3Qgb25seSBjb250YWluaW5nIHRoZSB3aGl0ZWxpc3RlZCBwcm9wZXJ0aWVzLlxuXHRfLnBpY2sgPSBmdW5jdGlvbiAob2JqZWN0LCBvaXRlcmF0ZWUsIGNvbnRleHQpIHtcblx0XHR2YXIgcmVzdWx0ID0ge30sIG9iaiA9IG9iamVjdCwgaXRlcmF0ZWUsIGtleXM7XG5cdFx0aWYgKG9iaiA9PSBudWxsKSByZXR1cm4gcmVzdWx0O1xuXHRcdGlmIChfLmlzRnVuY3Rpb24ob2l0ZXJhdGVlKSkge1xuXHRcdFx0a2V5cyA9IF8uYWxsS2V5cyhvYmopO1xuXHRcdFx0aXRlcmF0ZWUgPSBvcHRpbWl6ZUNiKG9pdGVyYXRlZSwgY29udGV4dCk7XG5cdFx0fSBlbHNlIHtcblx0XHRcdGtleXMgPSBmbGF0dGVuKGFyZ3VtZW50cywgZmFsc2UsIGZhbHNlLCAxKTtcblx0XHRcdGl0ZXJhdGVlID0gZnVuY3Rpb24gKHZhbHVlLCBrZXksIG9iaikgeyByZXR1cm4ga2V5IGluIG9iajsgfTtcblx0XHRcdG9iaiA9IE9iamVjdChvYmopO1xuXHRcdH1cblx0XHRmb3IgKHZhciBpID0gMCwgbGVuZ3RoID0ga2V5cy5sZW5ndGg7IGkgPCBsZW5ndGg7IGkrKykge1xuXHRcdFx0dmFyIGtleSA9IGtleXNbaV07XG5cdFx0XHR2YXIgdmFsdWUgPSBvYmpba2V5XTtcblx0XHRcdGlmIChpdGVyYXRlZSh2YWx1ZSwga2V5LCBvYmopKSByZXN1bHRba2V5XSA9IHZhbHVlO1xuXHRcdH1cblx0XHRyZXR1cm4gcmVzdWx0O1xuXHR9O1xuXG5cdC8vIFJldHVybiBhIGNvcHkgb2YgdGhlIG9iamVjdCB3aXRob3V0IHRoZSBibGFja2xpc3RlZCBwcm9wZXJ0aWVzLlxuXHRfLm9taXQgPSBmdW5jdGlvbiAob2JqLCBpdGVyYXRlZSwgY29udGV4dCkge1xuXHRcdGlmIChfLmlzRnVuY3Rpb24oaXRlcmF0ZWUpKSB7XG5cdFx0XHRpdGVyYXRlZSA9IF8ubmVnYXRlKGl0ZXJhdGVlKTtcblx0XHR9IGVsc2Uge1xuXHRcdFx0dmFyIGtleXMgPSBfLm1hcChmbGF0dGVuKGFyZ3VtZW50cywgZmFsc2UsIGZhbHNlLCAxKSwgU3RyaW5nKTtcblx0XHRcdGl0ZXJhdGVlID0gZnVuY3Rpb24gKHZhbHVlLCBrZXkpIHtcblx0XHRcdFx0cmV0dXJuICFfLmNvbnRhaW5zKGtleXMsIGtleSk7XG5cdFx0XHR9O1xuXHRcdH1cblx0XHRyZXR1cm4gXy5waWNrKG9iaiwgaXRlcmF0ZWUsIGNvbnRleHQpO1xuXHR9O1xuXG5cdC8vIEZpbGwgaW4gYSBnaXZlbiBvYmplY3Qgd2l0aCBkZWZhdWx0IHByb3BlcnRpZXMuXG5cdF8uZGVmYXVsdHMgPSBjcmVhdGVBc3NpZ25lcihfLmFsbEtleXMsIHRydWUpO1xuXG5cdC8vIENyZWF0ZXMgYW4gb2JqZWN0IHRoYXQgaW5oZXJpdHMgZnJvbSB0aGUgZ2l2ZW4gcHJvdG90eXBlIG9iamVjdC5cblx0Ly8gSWYgYWRkaXRpb25hbCBwcm9wZXJ0aWVzIGFyZSBwcm92aWRlZCB0aGVuIHRoZXkgd2lsbCBiZSBhZGRlZCB0byB0aGVcblx0Ly8gY3JlYXRlZCBvYmplY3QuXG5cdF8uY3JlYXRlID0gZnVuY3Rpb24gKHByb3RvdHlwZSwgcHJvcHMpIHtcblx0XHR2YXIgcmVzdWx0ID0gYmFzZUNyZWF0ZShwcm90b3R5cGUpO1xuXHRcdGlmIChwcm9wcykgXy5leHRlbmRPd24ocmVzdWx0LCBwcm9wcyk7XG5cdFx0cmV0dXJuIHJlc3VsdDtcblx0fTtcblxuXHQvLyBDcmVhdGUgYSAoc2hhbGxvdy1jbG9uZWQpIGR1cGxpY2F0ZSBvZiBhbiBvYmplY3QuXG5cdF8uY2xvbmUgPSBmdW5jdGlvbiAob2JqKSB7XG5cdFx0aWYgKCFfLmlzT2JqZWN0KG9iaikpIHJldHVybiBvYmo7XG5cdFx0cmV0dXJuIF8uaXNBcnJheShvYmopID8gb2JqLnNsaWNlKCkgOiBfLmV4dGVuZCh7fSwgb2JqKTtcblx0fTtcblxuXHQvLyBJbnZva2VzIGludGVyY2VwdG9yIHdpdGggdGhlIG9iaiwgYW5kIHRoZW4gcmV0dXJucyBvYmouXG5cdC8vIFRoZSBwcmltYXJ5IHB1cnBvc2Ugb2YgdGhpcyBtZXRob2QgaXMgdG8gXCJ0YXAgaW50b1wiIGEgbWV0aG9kIGNoYWluLCBpblxuXHQvLyBvcmRlciB0byBwZXJmb3JtIG9wZXJhdGlvbnMgb24gaW50ZXJtZWRpYXRlIHJlc3VsdHMgd2l0aGluIHRoZSBjaGFpbi5cblx0Xy50YXAgPSBmdW5jdGlvbiAob2JqLCBpbnRlcmNlcHRvcikge1xuXHRcdGludGVyY2VwdG9yKG9iaik7XG5cdFx0cmV0dXJuIG9iajtcblx0fTtcblxuXHQvLyBSZXR1cm5zIHdoZXRoZXIgYW4gb2JqZWN0IGhhcyBhIGdpdmVuIHNldCBvZiBga2V5OnZhbHVlYCBwYWlycy5cblx0Xy5pc01hdGNoID0gZnVuY3Rpb24gKG9iamVjdCwgYXR0cnMpIHtcblx0XHR2YXIga2V5cyA9IF8ua2V5cyhhdHRycyksIGxlbmd0aCA9IGtleXMubGVuZ3RoO1xuXHRcdGlmIChvYmplY3QgPT0gbnVsbCkgcmV0dXJuICFsZW5ndGg7XG5cdFx0dmFyIG9iaiA9IE9iamVjdChvYmplY3QpO1xuXHRcdGZvciAodmFyIGkgPSAwOyBpIDwgbGVuZ3RoOyBpKyspIHtcblx0XHRcdHZhciBrZXkgPSBrZXlzW2ldO1xuXHRcdFx0aWYgKGF0dHJzW2tleV0gIT09IG9ialtrZXldIHx8ICEoa2V5IGluIG9iaikpIHJldHVybiBmYWxzZTtcblx0XHR9XG5cdFx0cmV0dXJuIHRydWU7XG5cdH07XG5cblxuXHQvLyBJbnRlcm5hbCByZWN1cnNpdmUgY29tcGFyaXNvbiBmdW5jdGlvbiBmb3IgYGlzRXF1YWxgLlxuXHR2YXIgZXEgPSBmdW5jdGlvbiAoYSwgYiwgYVN0YWNrLCBiU3RhY2spIHtcblx0XHQvLyBJZGVudGljYWwgb2JqZWN0cyBhcmUgZXF1YWwuIGAwID09PSAtMGAsIGJ1dCB0aGV5IGFyZW4ndCBpZGVudGljYWwuXG5cdFx0Ly8gU2VlIHRoZSBbSGFybW9ueSBgZWdhbGAgcHJvcG9zYWxdKGh0dHA6Ly93aWtpLmVjbWFzY3JpcHQub3JnL2Rva3UucGhwP2lkPWhhcm1vbnk6ZWdhbCkuXG5cdFx0aWYgKGEgPT09IGIpIHJldHVybiBhICE9PSAwIHx8IDEgLyBhID09PSAxIC8gYjtcblx0XHQvLyBBIHN0cmljdCBjb21wYXJpc29uIGlzIG5lY2Vzc2FyeSBiZWNhdXNlIGBudWxsID09IHVuZGVmaW5lZGAuXG5cdFx0aWYgKGEgPT0gbnVsbCB8fCBiID09IG51bGwpIHJldHVybiBhID09PSBiO1xuXHRcdC8vIFVud3JhcCBhbnkgd3JhcHBlZCBvYmplY3RzLlxuXHRcdGlmIChhIGluc3RhbmNlb2YgXykgYSA9IGEuX3dyYXBwZWQ7XG5cdFx0aWYgKGIgaW5zdGFuY2VvZiBfKSBiID0gYi5fd3JhcHBlZDtcblx0XHQvLyBDb21wYXJlIGBbW0NsYXNzXV1gIG5hbWVzLlxuXHRcdHZhciBjbGFzc05hbWUgPSB0b1N0cmluZy5jYWxsKGEpO1xuXHRcdGlmIChjbGFzc05hbWUgIT09IHRvU3RyaW5nLmNhbGwoYikpIHJldHVybiBmYWxzZTtcblx0XHRzd2l0Y2ggKGNsYXNzTmFtZSkge1xuXHRcdFx0Ly8gU3RyaW5ncywgbnVtYmVycywgcmVndWxhciBleHByZXNzaW9ucywgZGF0ZXMsIGFuZCBib29sZWFucyBhcmUgY29tcGFyZWQgYnkgdmFsdWUuXG5cdFx0XHRjYXNlICdbb2JqZWN0IFJlZ0V4cF0nOlxuXHRcdFx0Ly8gUmVnRXhwcyBhcmUgY29lcmNlZCB0byBzdHJpbmdzIGZvciBjb21wYXJpc29uIChOb3RlOiAnJyArIC9hL2kgPT09ICcvYS9pJylcblx0XHRcdGNhc2UgJ1tvYmplY3QgU3RyaW5nXSc6XG5cdFx0XHRcdC8vIFByaW1pdGl2ZXMgYW5kIHRoZWlyIGNvcnJlc3BvbmRpbmcgb2JqZWN0IHdyYXBwZXJzIGFyZSBlcXVpdmFsZW50OyB0aHVzLCBgXCI1XCJgIGlzXG5cdFx0XHRcdC8vIGVxdWl2YWxlbnQgdG8gYG5ldyBTdHJpbmcoXCI1XCIpYC5cblx0XHRcdFx0cmV0dXJuICcnICsgYSA9PT0gJycgKyBiO1xuXHRcdFx0Y2FzZSAnW29iamVjdCBOdW1iZXJdJzpcblx0XHRcdFx0Ly8gYE5hTmBzIGFyZSBlcXVpdmFsZW50LCBidXQgbm9uLXJlZmxleGl2ZS5cblx0XHRcdFx0Ly8gT2JqZWN0KE5hTikgaXMgZXF1aXZhbGVudCB0byBOYU5cblx0XHRcdFx0aWYgKCthICE9PSArYSkgcmV0dXJuICtiICE9PSArYjtcblx0XHRcdFx0Ly8gQW4gYGVnYWxgIGNvbXBhcmlzb24gaXMgcGVyZm9ybWVkIGZvciBvdGhlciBudW1lcmljIHZhbHVlcy5cblx0XHRcdFx0cmV0dXJuICthID09PSAwID8gMSAvICthID09PSAxIC8gYiA6ICthID09PSArYjtcblx0XHRcdGNhc2UgJ1tvYmplY3QgRGF0ZV0nOlxuXHRcdFx0Y2FzZSAnW29iamVjdCBCb29sZWFuXSc6XG5cdFx0XHRcdC8vIENvZXJjZSBkYXRlcyBhbmQgYm9vbGVhbnMgdG8gbnVtZXJpYyBwcmltaXRpdmUgdmFsdWVzLiBEYXRlcyBhcmUgY29tcGFyZWQgYnkgdGhlaXJcblx0XHRcdFx0Ly8gbWlsbGlzZWNvbmQgcmVwcmVzZW50YXRpb25zLiBOb3RlIHRoYXQgaW52YWxpZCBkYXRlcyB3aXRoIG1pbGxpc2Vjb25kIHJlcHJlc2VudGF0aW9uc1xuXHRcdFx0XHQvLyBvZiBgTmFOYCBhcmUgbm90IGVxdWl2YWxlbnQuXG5cdFx0XHRcdHJldHVybiArYSA9PT0gK2I7XG5cdFx0fVxuXG5cdFx0dmFyIGFyZUFycmF5cyA9IGNsYXNzTmFtZSA9PT0gJ1tvYmplY3QgQXJyYXldJztcblx0XHRpZiAoIWFyZUFycmF5cykge1xuXHRcdFx0aWYgKHR5cGVvZiBhICE9ICdvYmplY3QnIHx8IHR5cGVvZiBiICE9ICdvYmplY3QnKSByZXR1cm4gZmFsc2U7XG5cblx0XHRcdC8vIE9iamVjdHMgd2l0aCBkaWZmZXJlbnQgY29uc3RydWN0b3JzIGFyZSBub3QgZXF1aXZhbGVudCwgYnV0IGBPYmplY3RgcyBvciBgQXJyYXlgc1xuXHRcdFx0Ly8gZnJvbSBkaWZmZXJlbnQgZnJhbWVzIGFyZS5cblx0XHRcdHZhciBhQ3RvciA9IGEuY29uc3RydWN0b3IsIGJDdG9yID0gYi5jb25zdHJ1Y3Rvcjtcblx0XHRcdGlmIChhQ3RvciAhPT0gYkN0b3IgJiYgIShfLmlzRnVuY3Rpb24oYUN0b3IpICYmIGFDdG9yIGluc3RhbmNlb2YgYUN0b3IgJiZcblx0XHRcdFx0Xy5pc0Z1bmN0aW9uKGJDdG9yKSAmJiBiQ3RvciBpbnN0YW5jZW9mIGJDdG9yKVxuXHRcdFx0XHQmJiAoJ2NvbnN0cnVjdG9yJyBpbiBhICYmICdjb25zdHJ1Y3RvcicgaW4gYikpIHtcblx0XHRcdFx0cmV0dXJuIGZhbHNlO1xuXHRcdFx0fVxuXHRcdH1cblx0XHQvLyBBc3N1bWUgZXF1YWxpdHkgZm9yIGN5Y2xpYyBzdHJ1Y3R1cmVzLiBUaGUgYWxnb3JpdGhtIGZvciBkZXRlY3RpbmcgY3ljbGljXG5cdFx0Ly8gc3RydWN0dXJlcyBpcyBhZGFwdGVkIGZyb20gRVMgNS4xIHNlY3Rpb24gMTUuMTIuMywgYWJzdHJhY3Qgb3BlcmF0aW9uIGBKT2AuXG5cblx0XHQvLyBJbml0aWFsaXppbmcgc3RhY2sgb2YgdHJhdmVyc2VkIG9iamVjdHMuXG5cdFx0Ly8gSXQncyBkb25lIGhlcmUgc2luY2Ugd2Ugb25seSBuZWVkIHRoZW0gZm9yIG9iamVjdHMgYW5kIGFycmF5cyBjb21wYXJpc29uLlxuXHRcdGFTdGFjayA9IGFTdGFjayB8fCBbXTtcblx0XHRiU3RhY2sgPSBiU3RhY2sgfHwgW107XG5cdFx0dmFyIGxlbmd0aCA9IGFTdGFjay5sZW5ndGg7XG5cdFx0d2hpbGUgKGxlbmd0aC0tKSB7XG5cdFx0XHQvLyBMaW5lYXIgc2VhcmNoLiBQZXJmb3JtYW5jZSBpcyBpbnZlcnNlbHkgcHJvcG9ydGlvbmFsIHRvIHRoZSBudW1iZXIgb2Zcblx0XHRcdC8vIHVuaXF1ZSBuZXN0ZWQgc3RydWN0dXJlcy5cblx0XHRcdGlmIChhU3RhY2tbbGVuZ3RoXSA9PT0gYSkgcmV0dXJuIGJTdGFja1tsZW5ndGhdID09PSBiO1xuXHRcdH1cblxuXHRcdC8vIEFkZCB0aGUgZmlyc3Qgb2JqZWN0IHRvIHRoZSBzdGFjayBvZiB0cmF2ZXJzZWQgb2JqZWN0cy5cblx0XHRhU3RhY2sucHVzaChhKTtcblx0XHRiU3RhY2sucHVzaChiKTtcblxuXHRcdC8vIFJlY3Vyc2l2ZWx5IGNvbXBhcmUgb2JqZWN0cyBhbmQgYXJyYXlzLlxuXHRcdGlmIChhcmVBcnJheXMpIHtcblx0XHRcdC8vIENvbXBhcmUgYXJyYXkgbGVuZ3RocyB0byBkZXRlcm1pbmUgaWYgYSBkZWVwIGNvbXBhcmlzb24gaXMgbmVjZXNzYXJ5LlxuXHRcdFx0bGVuZ3RoID0gYS5sZW5ndGg7XG5cdFx0XHRpZiAobGVuZ3RoICE9PSBiLmxlbmd0aCkgcmV0dXJuIGZhbHNlO1xuXHRcdFx0Ly8gRGVlcCBjb21wYXJlIHRoZSBjb250ZW50cywgaWdub3Jpbmcgbm9uLW51bWVyaWMgcHJvcGVydGllcy5cblx0XHRcdHdoaWxlIChsZW5ndGgtLSkge1xuXHRcdFx0XHRpZiAoIWVxKGFbbGVuZ3RoXSwgYltsZW5ndGhdLCBhU3RhY2ssIGJTdGFjaykpIHJldHVybiBmYWxzZTtcblx0XHRcdH1cblx0XHR9IGVsc2Uge1xuXHRcdFx0Ly8gRGVlcCBjb21wYXJlIG9iamVjdHMuXG5cdFx0XHR2YXIga2V5cyA9IF8ua2V5cyhhKSwga2V5O1xuXHRcdFx0bGVuZ3RoID0ga2V5cy5sZW5ndGg7XG5cdFx0XHQvLyBFbnN1cmUgdGhhdCBib3RoIG9iamVjdHMgY29udGFpbiB0aGUgc2FtZSBudW1iZXIgb2YgcHJvcGVydGllcyBiZWZvcmUgY29tcGFyaW5nIGRlZXAgZXF1YWxpdHkuXG5cdFx0XHRpZiAoXy5rZXlzKGIpLmxlbmd0aCAhPT0gbGVuZ3RoKSByZXR1cm4gZmFsc2U7XG5cdFx0XHR3aGlsZSAobGVuZ3RoLS0pIHtcblx0XHRcdFx0Ly8gRGVlcCBjb21wYXJlIGVhY2ggbWVtYmVyXG5cdFx0XHRcdGtleSA9IGtleXNbbGVuZ3RoXTtcblx0XHRcdFx0aWYgKCEoXy5oYXMoYiwga2V5KSAmJiBlcShhW2tleV0sIGJba2V5XSwgYVN0YWNrLCBiU3RhY2spKSkgcmV0dXJuIGZhbHNlO1xuXHRcdFx0fVxuXHRcdH1cblx0XHQvLyBSZW1vdmUgdGhlIGZpcnN0IG9iamVjdCBmcm9tIHRoZSBzdGFjayBvZiB0cmF2ZXJzZWQgb2JqZWN0cy5cblx0XHRhU3RhY2sucG9wKCk7XG5cdFx0YlN0YWNrLnBvcCgpO1xuXHRcdHJldHVybiB0cnVlO1xuXHR9O1xuXG5cdC8vIFBlcmZvcm0gYSBkZWVwIGNvbXBhcmlzb24gdG8gY2hlY2sgaWYgdHdvIG9iamVjdHMgYXJlIGVxdWFsLlxuXHRfLmlzRXF1YWwgPSBmdW5jdGlvbiAoYSwgYikge1xuXHRcdHJldHVybiBlcShhLCBiKTtcblx0fTtcblxuXHQvLyBJcyBhIGdpdmVuIGFycmF5LCBzdHJpbmcsIG9yIG9iamVjdCBlbXB0eT9cblx0Ly8gQW4gXCJlbXB0eVwiIG9iamVjdCBoYXMgbm8gZW51bWVyYWJsZSBvd24tcHJvcGVydGllcy5cblx0Xy5pc0VtcHR5ID0gZnVuY3Rpb24gKG9iaikge1xuXHRcdGlmIChvYmogPT0gbnVsbCkgcmV0dXJuIHRydWU7XG5cdFx0aWYgKGlzQXJyYXlMaWtlKG9iaikgJiYgKF8uaXNBcnJheShvYmopIHx8IF8uaXNTdHJpbmcob2JqKSB8fCBfLmlzQXJndW1lbnRzKG9iaikpKSByZXR1cm4gb2JqLmxlbmd0aCA9PT0gMDtcblx0XHRyZXR1cm4gXy5rZXlzKG9iaikubGVuZ3RoID09PSAwO1xuXHR9O1xuXG5cdC8vIElzIGEgZ2l2ZW4gdmFsdWUgYSBET00gZWxlbWVudD9cblx0Xy5pc0VsZW1lbnQgPSBmdW5jdGlvbiAob2JqKSB7XG5cdFx0cmV0dXJuICEhKG9iaiAmJiBvYmoubm9kZVR5cGUgPT09IDEpO1xuXHR9O1xuXG5cdC8vIElzIGEgZ2l2ZW4gdmFsdWUgYW4gYXJyYXk/XG5cdC8vIERlbGVnYXRlcyB0byBFQ01BNSdzIG5hdGl2ZSBBcnJheS5pc0FycmF5XG5cdF8uaXNBcnJheSA9IG5hdGl2ZUlzQXJyYXkgfHwgZnVuY3Rpb24gKG9iaikge1xuXHRcdHJldHVybiB0b1N0cmluZy5jYWxsKG9iaikgPT09ICdbb2JqZWN0IEFycmF5XSc7XG5cdH07XG5cblx0Ly8gSXMgYSBnaXZlbiB2YXJpYWJsZSBhbiBvYmplY3Q/XG5cdF8uaXNPYmplY3QgPSBmdW5jdGlvbiAob2JqKSB7XG5cdFx0dmFyIHR5cGUgPSB0eXBlb2Ygb2JqO1xuXHRcdHJldHVybiB0eXBlID09PSAnZnVuY3Rpb24nIHx8IHR5cGUgPT09ICdvYmplY3QnICYmICEhb2JqO1xuXHR9O1xuXG5cdC8vIEFkZCBzb21lIGlzVHlwZSBtZXRob2RzOiBpc0FyZ3VtZW50cywgaXNGdW5jdGlvbiwgaXNTdHJpbmcsIGlzTnVtYmVyLCBpc0RhdGUsIGlzUmVnRXhwLCBpc0Vycm9yLlxuXHRfLmVhY2goWydBcmd1bWVudHMnLCAnRnVuY3Rpb24nLCAnU3RyaW5nJywgJ051bWJlcicsICdEYXRlJywgJ1JlZ0V4cCcsICdFcnJvciddLCBmdW5jdGlvbiAobmFtZSkge1xuXHRcdF9bJ2lzJyArIG5hbWVdID0gZnVuY3Rpb24gKG9iaikge1xuXHRcdFx0cmV0dXJuIHRvU3RyaW5nLmNhbGwob2JqKSA9PT0gJ1tvYmplY3QgJyArIG5hbWUgKyAnXSc7XG5cdFx0fTtcblx0fSk7XG5cblx0Ly8gRGVmaW5lIGEgZmFsbGJhY2sgdmVyc2lvbiBvZiB0aGUgbWV0aG9kIGluIGJyb3dzZXJzIChhaGVtLCBJRSA8IDkpLCB3aGVyZVxuXHQvLyB0aGVyZSBpc24ndCBhbnkgaW5zcGVjdGFibGUgXCJBcmd1bWVudHNcIiB0eXBlLlxuXHRpZiAoIV8uaXNBcmd1bWVudHMoYXJndW1lbnRzKSkge1xuXHRcdF8uaXNBcmd1bWVudHMgPSBmdW5jdGlvbiAob2JqKSB7XG5cdFx0XHRyZXR1cm4gXy5oYXMob2JqLCAnY2FsbGVlJyk7XG5cdFx0fTtcblx0fVxuXG5cdC8vIE9wdGltaXplIGBpc0Z1bmN0aW9uYCBpZiBhcHByb3ByaWF0ZS4gV29yayBhcm91bmQgc29tZSB0eXBlb2YgYnVncyBpbiBvbGQgdjgsXG5cdC8vIElFIDExICgjMTYyMSksIGFuZCBpbiBTYWZhcmkgOCAoIzE5MjkpLlxuXHRpZiAodHlwZW9mIC8uLyAhPSAnZnVuY3Rpb24nICYmIHR5cGVvZiBJbnQ4QXJyYXkgIT0gJ29iamVjdCcpIHtcblx0XHRfLmlzRnVuY3Rpb24gPSBmdW5jdGlvbiAob2JqKSB7XG5cdFx0XHRyZXR1cm4gdHlwZW9mIG9iaiA9PSAnZnVuY3Rpb24nIHx8IGZhbHNlO1xuXHRcdH07XG5cdH1cblxuXHQvLyBJcyBhIGdpdmVuIG9iamVjdCBhIGZpbml0ZSBudW1iZXI/XG5cdF8uaXNGaW5pdGUgPSBmdW5jdGlvbiAob2JqKSB7XG5cdFx0cmV0dXJuIGlzRmluaXRlKG9iaikgJiYgIWlzTmFOKHBhcnNlRmxvYXQob2JqKSk7XG5cdH07XG5cblx0Ly8gSXMgdGhlIGdpdmVuIHZhbHVlIGBOYU5gPyAoTmFOIGlzIHRoZSBvbmx5IG51bWJlciB3aGljaCBkb2VzIG5vdCBlcXVhbCBpdHNlbGYpLlxuXHRfLmlzTmFOID0gZnVuY3Rpb24gKG9iaikge1xuXHRcdHJldHVybiBfLmlzTnVtYmVyKG9iaikgJiYgb2JqICE9PSArb2JqO1xuXHR9O1xuXG5cdC8vIElzIGEgZ2l2ZW4gdmFsdWUgYSBib29sZWFuP1xuXHRfLmlzQm9vbGVhbiA9IGZ1bmN0aW9uIChvYmopIHtcblx0XHRyZXR1cm4gb2JqID09PSB0cnVlIHx8IG9iaiA9PT0gZmFsc2UgfHwgdG9TdHJpbmcuY2FsbChvYmopID09PSAnW29iamVjdCBCb29sZWFuXSc7XG5cdH07XG5cblx0Ly8gSXMgYSBnaXZlbiB2YWx1ZSBlcXVhbCB0byBudWxsP1xuXHRfLmlzTnVsbCA9IGZ1bmN0aW9uIChvYmopIHtcblx0XHRyZXR1cm4gb2JqID09PSBudWxsO1xuXHR9O1xuXG5cdC8vIElzIGEgZ2l2ZW4gdmFyaWFibGUgdW5kZWZpbmVkP1xuXHRfLmlzVW5kZWZpbmVkID0gZnVuY3Rpb24gKG9iaikge1xuXHRcdHJldHVybiBvYmogPT09IHZvaWQgMDtcblx0fTtcblxuXHQvLyBTaG9ydGN1dCBmdW5jdGlvbiBmb3IgY2hlY2tpbmcgaWYgYW4gb2JqZWN0IGhhcyBhIGdpdmVuIHByb3BlcnR5IGRpcmVjdGx5XG5cdC8vIG9uIGl0c2VsZiAoaW4gb3RoZXIgd29yZHMsIG5vdCBvbiBhIHByb3RvdHlwZSkuXG5cdF8uaGFzID0gZnVuY3Rpb24gKG9iaiwga2V5KSB7XG5cdFx0cmV0dXJuIG9iaiAhPSBudWxsICYmIGhhc093blByb3BlcnR5LmNhbGwob2JqLCBrZXkpO1xuXHR9O1xuXG5cdC8vIFV0aWxpdHkgRnVuY3Rpb25zXG5cdC8vIC0tLS0tLS0tLS0tLS0tLS0tXG5cblx0Ly8gUnVuIFVuZGVyc2NvcmUuanMgaW4gKm5vQ29uZmxpY3QqIG1vZGUsIHJldHVybmluZyB0aGUgYF9gIHZhcmlhYmxlIHRvIGl0c1xuXHQvLyBwcmV2aW91cyBvd25lci4gUmV0dXJucyBhIHJlZmVyZW5jZSB0byB0aGUgVW5kZXJzY29yZSBvYmplY3QuXG5cdF8ubm9Db25mbGljdCA9IGZ1bmN0aW9uICgpIHtcblx0XHRyb290Ll8gPSBwcmV2aW91c1VuZGVyc2NvcmU7XG5cdFx0cmV0dXJuIHRoaXM7XG5cdH07XG5cblx0Ly8gS2VlcCB0aGUgaWRlbnRpdHkgZnVuY3Rpb24gYXJvdW5kIGZvciBkZWZhdWx0IGl0ZXJhdGVlcy5cblx0Xy5pZGVudGl0eSA9IGZ1bmN0aW9uICh2YWx1ZSkge1xuXHRcdHJldHVybiB2YWx1ZTtcblx0fTtcblxuXHQvLyBQcmVkaWNhdGUtZ2VuZXJhdGluZyBmdW5jdGlvbnMuIE9mdGVuIHVzZWZ1bCBvdXRzaWRlIG9mIFVuZGVyc2NvcmUuXG5cdF8uY29uc3RhbnQgPSBmdW5jdGlvbiAodmFsdWUpIHtcblx0XHRyZXR1cm4gZnVuY3Rpb24gKCkge1xuXHRcdFx0cmV0dXJuIHZhbHVlO1xuXHRcdH07XG5cdH07XG5cblx0Xy5ub29wID0gZnVuY3Rpb24gKCkgeyB9O1xuXG5cdF8ucHJvcGVydHkgPSBmdW5jdGlvbiAoa2V5KSB7XG5cdFx0cmV0dXJuIGZ1bmN0aW9uIChvYmopIHtcblx0XHRcdHJldHVybiBvYmogPT0gbnVsbCA/IHZvaWQgMCA6IG9ialtrZXldO1xuXHRcdH07XG5cdH07XG5cblx0Ly8gR2VuZXJhdGVzIGEgZnVuY3Rpb24gZm9yIGEgZ2l2ZW4gb2JqZWN0IHRoYXQgcmV0dXJucyBhIGdpdmVuIHByb3BlcnR5LlxuXHRfLnByb3BlcnR5T2YgPSBmdW5jdGlvbiAob2JqKSB7XG5cdFx0cmV0dXJuIG9iaiA9PSBudWxsID8gZnVuY3Rpb24gKCkgeyB9IDogZnVuY3Rpb24gKGtleSkge1xuXHRcdFx0cmV0dXJuIG9ialtrZXldO1xuXHRcdH07XG5cdH07XG5cblx0Ly8gUmV0dXJucyBhIHByZWRpY2F0ZSBmb3IgY2hlY2tpbmcgd2hldGhlciBhbiBvYmplY3QgaGFzIGEgZ2l2ZW4gc2V0IG9mIFxuXHQvLyBga2V5OnZhbHVlYCBwYWlycy5cblx0Xy5tYXRjaGVyID0gXy5tYXRjaGVzID0gZnVuY3Rpb24gKGF0dHJzKSB7XG5cdFx0YXR0cnMgPSBfLmV4dGVuZE93bih7fSwgYXR0cnMpO1xuXHRcdHJldHVybiBmdW5jdGlvbiAob2JqKSB7XG5cdFx0XHRyZXR1cm4gXy5pc01hdGNoKG9iaiwgYXR0cnMpO1xuXHRcdH07XG5cdH07XG5cblx0Ly8gUnVuIGEgZnVuY3Rpb24gKipuKiogdGltZXMuXG5cdF8udGltZXMgPSBmdW5jdGlvbiAobiwgaXRlcmF0ZWUsIGNvbnRleHQpIHtcblx0XHR2YXIgYWNjdW0gPSBBcnJheShNYXRoLm1heCgwLCBuKSk7XG5cdFx0aXRlcmF0ZWUgPSBvcHRpbWl6ZUNiKGl0ZXJhdGVlLCBjb250ZXh0LCAxKTtcblx0XHRmb3IgKHZhciBpID0gMDsgaSA8IG47IGkrKykgYWNjdW1baV0gPSBpdGVyYXRlZShpKTtcblx0XHRyZXR1cm4gYWNjdW07XG5cdH07XG5cblx0Ly8gUmV0dXJuIGEgcmFuZG9tIGludGVnZXIgYmV0d2VlbiBtaW4gYW5kIG1heCAoaW5jbHVzaXZlKS5cblx0Xy5yYW5kb20gPSBmdW5jdGlvbiAobWluLCBtYXgpIHtcblx0XHRpZiAobWF4ID09IG51bGwpIHtcblx0XHRcdG1heCA9IG1pbjtcblx0XHRcdG1pbiA9IDA7XG5cdFx0fVxuXHRcdHJldHVybiBtaW4gKyBNYXRoLmZsb29yKE1hdGgucmFuZG9tKCkgKiAobWF4IC0gbWluICsgMSkpO1xuXHR9O1xuXG5cdC8vIEEgKHBvc3NpYmx5IGZhc3Rlcikgd2F5IHRvIGdldCB0aGUgY3VycmVudCB0aW1lc3RhbXAgYXMgYW4gaW50ZWdlci5cblx0Xy5ub3cgPSBEYXRlLm5vdyB8fCBmdW5jdGlvbiAoKSB7XG5cdFx0cmV0dXJuIG5ldyBEYXRlKCkuZ2V0VGltZSgpO1xuXHR9O1xuXG5cdC8vIExpc3Qgb2YgSFRNTCBlbnRpdGllcyBmb3IgZXNjYXBpbmcuXG5cdHZhciBlc2NhcGVNYXAgPSB7XG5cdFx0JyYnOiAnJmFtcDsnLFxuXHRcdCc8JzogJyZsdDsnLFxuXHRcdCc+JzogJyZndDsnLFxuXHRcdCdcIic6ICcmcXVvdDsnLFxuXHRcdFwiJ1wiOiAnJiN4Mjc7Jyxcblx0XHQnYCc6ICcmI3g2MDsnXG5cdH07XG5cdHZhciB1bmVzY2FwZU1hcCA9IF8uaW52ZXJ0KGVzY2FwZU1hcCk7XG5cblx0Ly8gRnVuY3Rpb25zIGZvciBlc2NhcGluZyBhbmQgdW5lc2NhcGluZyBzdHJpbmdzIHRvL2Zyb20gSFRNTCBpbnRlcnBvbGF0aW9uLlxuXHR2YXIgY3JlYXRlRXNjYXBlciA9IGZ1bmN0aW9uIChtYXApIHtcblx0XHR2YXIgZXNjYXBlciA9IGZ1bmN0aW9uIChtYXRjaCkge1xuXHRcdFx0cmV0dXJuIG1hcFttYXRjaF07XG5cdFx0fTtcblx0XHQvLyBSZWdleGVzIGZvciBpZGVudGlmeWluZyBhIGtleSB0aGF0IG5lZWRzIHRvIGJlIGVzY2FwZWRcblx0XHR2YXIgc291cmNlID0gJyg/OicgKyBfLmtleXMobWFwKS5qb2luKCd8JykgKyAnKSc7XG5cdFx0dmFyIHRlc3RSZWdleHAgPSBSZWdFeHAoc291cmNlKTtcblx0XHR2YXIgcmVwbGFjZVJlZ2V4cCA9IFJlZ0V4cChzb3VyY2UsICdnJyk7XG5cdFx0cmV0dXJuIGZ1bmN0aW9uIChzdHJpbmcpIHtcblx0XHRcdHN0cmluZyA9IHN0cmluZyA9PSBudWxsID8gJycgOiAnJyArIHN0cmluZztcblx0XHRcdHJldHVybiB0ZXN0UmVnZXhwLnRlc3Qoc3RyaW5nKSA/IHN0cmluZy5yZXBsYWNlKHJlcGxhY2VSZWdleHAsIGVzY2FwZXIpIDogc3RyaW5nO1xuXHRcdH07XG5cdH07XG5cdF8uZXNjYXBlID0gY3JlYXRlRXNjYXBlcihlc2NhcGVNYXApO1xuXHRfLnVuZXNjYXBlID0gY3JlYXRlRXNjYXBlcih1bmVzY2FwZU1hcCk7XG5cblx0Ly8gSWYgdGhlIHZhbHVlIG9mIHRoZSBuYW1lZCBgcHJvcGVydHlgIGlzIGEgZnVuY3Rpb24gdGhlbiBpbnZva2UgaXQgd2l0aCB0aGVcblx0Ly8gYG9iamVjdGAgYXMgY29udGV4dDsgb3RoZXJ3aXNlLCByZXR1cm4gaXQuXG5cdF8ucmVzdWx0ID0gZnVuY3Rpb24gKG9iamVjdCwgcHJvcGVydHksIGZhbGxiYWNrKSB7XG5cdFx0dmFyIHZhbHVlID0gb2JqZWN0ID09IG51bGwgPyB2b2lkIDAgOiBvYmplY3RbcHJvcGVydHldO1xuXHRcdGlmICh2YWx1ZSA9PT0gdm9pZCAwKSB7XG5cdFx0XHR2YWx1ZSA9IGZhbGxiYWNrO1xuXHRcdH1cblx0XHRyZXR1cm4gXy5pc0Z1bmN0aW9uKHZhbHVlKSA/IHZhbHVlLmNhbGwob2JqZWN0KSA6IHZhbHVlO1xuXHR9O1xuXG5cdC8vIEdlbmVyYXRlIGEgdW5pcXVlIGludGVnZXIgaWQgKHVuaXF1ZSB3aXRoaW4gdGhlIGVudGlyZSBjbGllbnQgc2Vzc2lvbikuXG5cdC8vIFVzZWZ1bCBmb3IgdGVtcG9yYXJ5IERPTSBpZHMuXG5cdHZhciBpZENvdW50ZXIgPSAwO1xuXHRfLnVuaXF1ZUlkID0gZnVuY3Rpb24gKHByZWZpeCkge1xuXHRcdHZhciBpZCA9ICsraWRDb3VudGVyICsgJyc7XG5cdFx0cmV0dXJuIHByZWZpeCA/IHByZWZpeCArIGlkIDogaWQ7XG5cdH07XG5cblx0Ly8gQnkgZGVmYXVsdCwgVW5kZXJzY29yZSB1c2VzIEVSQi1zdHlsZSB0ZW1wbGF0ZSBkZWxpbWl0ZXJzLCBjaGFuZ2UgdGhlXG5cdC8vIGZvbGxvd2luZyB0ZW1wbGF0ZSBzZXR0aW5ncyB0byB1c2UgYWx0ZXJuYXRpdmUgZGVsaW1pdGVycy5cblx0Xy50ZW1wbGF0ZVNldHRpbmdzID0ge1xuXHRcdGV2YWx1YXRlOiAvPCUoW1xcc1xcU10rPyklPi9nLFxuXHRcdGludGVycG9sYXRlOiAvPCU9KFtcXHNcXFNdKz8pJT4vZyxcblx0XHRlc2NhcGU6IC88JS0oW1xcc1xcU10rPyklPi9nXG5cdH07XG5cblx0Ly8gV2hlbiBjdXN0b21pemluZyBgdGVtcGxhdGVTZXR0aW5nc2AsIGlmIHlvdSBkb24ndCB3YW50IHRvIGRlZmluZSBhblxuXHQvLyBpbnRlcnBvbGF0aW9uLCBldmFsdWF0aW9uIG9yIGVzY2FwaW5nIHJlZ2V4LCB3ZSBuZWVkIG9uZSB0aGF0IGlzXG5cdC8vIGd1YXJhbnRlZWQgbm90IHRvIG1hdGNoLlxuXHR2YXIgbm9NYXRjaCA9IC8oLileLztcblxuXHQvLyBDZXJ0YWluIGNoYXJhY3RlcnMgbmVlZCB0byBiZSBlc2NhcGVkIHNvIHRoYXQgdGhleSBjYW4gYmUgcHV0IGludG8gYVxuXHQvLyBzdHJpbmcgbGl0ZXJhbC5cblx0dmFyIGVzY2FwZXMgPSB7XG5cdFx0XCInXCI6IFwiJ1wiLFxuXHRcdCdcXFxcJzogJ1xcXFwnLFxuXHRcdCdcXHInOiAncicsXG5cdFx0J1xcbic6ICduJyxcblx0XHQnXFx1MjAyOCc6ICd1MjAyOCcsXG5cdFx0J1xcdTIwMjknOiAndTIwMjknXG5cdH07XG5cblx0dmFyIGVzY2FwZXIgPSAvXFxcXHwnfFxccnxcXG58XFx1MjAyOHxcXHUyMDI5L2c7XG5cblx0dmFyIGVzY2FwZUNoYXIgPSBmdW5jdGlvbiAobWF0Y2gpIHtcblx0XHRyZXR1cm4gJ1xcXFwnICsgZXNjYXBlc1ttYXRjaF07XG5cdH07XG5cblx0Ly8gSmF2YVNjcmlwdCBtaWNyby10ZW1wbGF0aW5nLCBzaW1pbGFyIHRvIEpvaG4gUmVzaWcncyBpbXBsZW1lbnRhdGlvbi5cblx0Ly8gVW5kZXJzY29yZSB0ZW1wbGF0aW5nIGhhbmRsZXMgYXJiaXRyYXJ5IGRlbGltaXRlcnMsIHByZXNlcnZlcyB3aGl0ZXNwYWNlLFxuXHQvLyBhbmQgY29ycmVjdGx5IGVzY2FwZXMgcXVvdGVzIHdpdGhpbiBpbnRlcnBvbGF0ZWQgY29kZS5cblx0Ly8gTkI6IGBvbGRTZXR0aW5nc2Agb25seSBleGlzdHMgZm9yIGJhY2t3YXJkcyBjb21wYXRpYmlsaXR5LlxuXHRfLnRlbXBsYXRlID0gZnVuY3Rpb24gKHRleHQsIHNldHRpbmdzLCBvbGRTZXR0aW5ncykge1xuXHRcdGlmICghc2V0dGluZ3MgJiYgb2xkU2V0dGluZ3MpIHNldHRpbmdzID0gb2xkU2V0dGluZ3M7XG5cdFx0c2V0dGluZ3MgPSBfLmRlZmF1bHRzKHt9LCBzZXR0aW5ncywgXy50ZW1wbGF0ZVNldHRpbmdzKTtcblxuXHRcdC8vIENvbWJpbmUgZGVsaW1pdGVycyBpbnRvIG9uZSByZWd1bGFyIGV4cHJlc3Npb24gdmlhIGFsdGVybmF0aW9uLlxuXHRcdHZhciBtYXRjaGVyID0gUmVnRXhwKFtcblx0XHRcdChzZXR0aW5ncy5lc2NhcGUgfHwgbm9NYXRjaCkuc291cmNlLFxuXHRcdFx0KHNldHRpbmdzLmludGVycG9sYXRlIHx8IG5vTWF0Y2gpLnNvdXJjZSxcblx0XHRcdChzZXR0aW5ncy5ldmFsdWF0ZSB8fCBub01hdGNoKS5zb3VyY2Vcblx0XHRdLmpvaW4oJ3wnKSArICd8JCcsICdnJyk7XG5cblx0XHQvLyBDb21waWxlIHRoZSB0ZW1wbGF0ZSBzb3VyY2UsIGVzY2FwaW5nIHN0cmluZyBsaXRlcmFscyBhcHByb3ByaWF0ZWx5LlxuXHRcdHZhciBpbmRleCA9IDA7XG5cdFx0dmFyIHNvdXJjZSA9IFwiX19wKz0nXCI7XG5cdFx0dGV4dC5yZXBsYWNlKG1hdGNoZXIsIGZ1bmN0aW9uIChtYXRjaCwgZXNjYXBlLCBpbnRlcnBvbGF0ZSwgZXZhbHVhdGUsIG9mZnNldCkge1xuXHRcdFx0c291cmNlICs9IHRleHQuc2xpY2UoaW5kZXgsIG9mZnNldCkucmVwbGFjZShlc2NhcGVyLCBlc2NhcGVDaGFyKTtcblx0XHRcdGluZGV4ID0gb2Zmc2V0ICsgbWF0Y2gubGVuZ3RoO1xuXG5cdFx0XHRpZiAoZXNjYXBlKSB7XG5cdFx0XHRcdHNvdXJjZSArPSBcIicrXFxuKChfX3Q9KFwiICsgZXNjYXBlICsgXCIpKT09bnVsbD8nJzpfLmVzY2FwZShfX3QpKStcXG4nXCI7XG5cdFx0XHR9IGVsc2UgaWYgKGludGVycG9sYXRlKSB7XG5cdFx0XHRcdHNvdXJjZSArPSBcIicrXFxuKChfX3Q9KFwiICsgaW50ZXJwb2xhdGUgKyBcIikpPT1udWxsPycnOl9fdCkrXFxuJ1wiO1xuXHRcdFx0fSBlbHNlIGlmIChldmFsdWF0ZSkge1xuXHRcdFx0XHRzb3VyY2UgKz0gXCInO1xcblwiICsgZXZhbHVhdGUgKyBcIlxcbl9fcCs9J1wiO1xuXHRcdFx0fVxuXG5cdFx0XHQvLyBBZG9iZSBWTXMgbmVlZCB0aGUgbWF0Y2ggcmV0dXJuZWQgdG8gcHJvZHVjZSB0aGUgY29ycmVjdCBvZmZlc3QuXG5cdFx0XHRyZXR1cm4gbWF0Y2g7XG5cdFx0fSk7XG5cdFx0c291cmNlICs9IFwiJztcXG5cIjtcblxuXHRcdC8vIElmIGEgdmFyaWFibGUgaXMgbm90IHNwZWNpZmllZCwgcGxhY2UgZGF0YSB2YWx1ZXMgaW4gbG9jYWwgc2NvcGUuXG5cdFx0aWYgKCFzZXR0aW5ncy52YXJpYWJsZSkgc291cmNlID0gJ3dpdGgob2JqfHx7fSl7XFxuJyArIHNvdXJjZSArICd9XFxuJztcblxuXHRcdHNvdXJjZSA9IFwidmFyIF9fdCxfX3A9JycsX19qPUFycmF5LnByb3RvdHlwZS5qb2luLFwiICtcblx0XHRcdFwicHJpbnQ9ZnVuY3Rpb24oKXtfX3ArPV9fai5jYWxsKGFyZ3VtZW50cywnJyk7fTtcXG5cIiArXG5cdFx0XHRzb3VyY2UgKyAncmV0dXJuIF9fcDtcXG4nO1xuXG5cdFx0dHJ5IHtcblx0XHRcdHZhciByZW5kZXIgPSBuZXcgRnVuY3Rpb24oc2V0dGluZ3MudmFyaWFibGUgfHwgJ29iaicsICdfJywgc291cmNlKTtcblx0XHR9IGNhdGNoIChlKSB7XG5cdFx0XHRlLnNvdXJjZSA9IHNvdXJjZTtcblx0XHRcdHRocm93IGU7XG5cdFx0fVxuXG5cdFx0dmFyIHRlbXBsYXRlID0gZnVuY3Rpb24gKGRhdGEpIHtcblx0XHRcdHJldHVybiByZW5kZXIuY2FsbCh0aGlzLCBkYXRhLCBfKTtcblx0XHR9O1xuXG5cdFx0Ly8gUHJvdmlkZSB0aGUgY29tcGlsZWQgc291cmNlIGFzIGEgY29udmVuaWVuY2UgZm9yIHByZWNvbXBpbGF0aW9uLlxuXHRcdHZhciBhcmd1bWVudCA9IHNldHRpbmdzLnZhcmlhYmxlIHx8ICdvYmonO1xuXHRcdHRlbXBsYXRlLnNvdXJjZSA9ICdmdW5jdGlvbignICsgYXJndW1lbnQgKyAnKXtcXG4nICsgc291cmNlICsgJ30nO1xuXG5cdFx0cmV0dXJuIHRlbXBsYXRlO1xuXHR9O1xuXG5cdC8vIEFkZCBhIFwiY2hhaW5cIiBmdW5jdGlvbi4gU3RhcnQgY2hhaW5pbmcgYSB3cmFwcGVkIFVuZGVyc2NvcmUgb2JqZWN0LlxuXHRfLmNoYWluID0gZnVuY3Rpb24gKG9iaikge1xuXHRcdHZhciBpbnN0YW5jZSA9IF8ob2JqKTtcblx0XHRpbnN0YW5jZS5fY2hhaW4gPSB0cnVlO1xuXHRcdHJldHVybiBpbnN0YW5jZTtcblx0fTtcblxuXHQvLyBPT1Bcblx0Ly8gLS0tLS0tLS0tLS0tLS0tXG5cdC8vIElmIFVuZGVyc2NvcmUgaXMgY2FsbGVkIGFzIGEgZnVuY3Rpb24sIGl0IHJldHVybnMgYSB3cmFwcGVkIG9iamVjdCB0aGF0XG5cdC8vIGNhbiBiZSB1c2VkIE9PLXN0eWxlLiBUaGlzIHdyYXBwZXIgaG9sZHMgYWx0ZXJlZCB2ZXJzaW9ucyBvZiBhbGwgdGhlXG5cdC8vIHVuZGVyc2NvcmUgZnVuY3Rpb25zLiBXcmFwcGVkIG9iamVjdHMgbWF5IGJlIGNoYWluZWQuXG5cblx0Ly8gSGVscGVyIGZ1bmN0aW9uIHRvIGNvbnRpbnVlIGNoYWluaW5nIGludGVybWVkaWF0ZSByZXN1bHRzLlxuXHR2YXIgcmVzdWx0ID0gZnVuY3Rpb24gKGluc3RhbmNlLCBvYmopIHtcblx0XHRyZXR1cm4gaW5zdGFuY2UuX2NoYWluID8gXyhvYmopLmNoYWluKCkgOiBvYmo7XG5cdH07XG5cblx0Ly8gQWRkIHlvdXIgb3duIGN1c3RvbSBmdW5jdGlvbnMgdG8gdGhlIFVuZGVyc2NvcmUgb2JqZWN0LlxuXHRfLm1peGluID0gZnVuY3Rpb24gKG9iaikge1xuXHRcdF8uZWFjaChfLmZ1bmN0aW9ucyhvYmopLCBmdW5jdGlvbiAobmFtZSkge1xuXHRcdFx0dmFyIGZ1bmMgPSBfW25hbWVdID0gb2JqW25hbWVdO1xuXHRcdFx0Xy5wcm90b3R5cGVbbmFtZV0gPSBmdW5jdGlvbiAoKSB7XG5cdFx0XHRcdHZhciBhcmdzID0gW3RoaXMuX3dyYXBwZWRdO1xuXHRcdFx0XHRwdXNoLmFwcGx5KGFyZ3MsIGFyZ3VtZW50cyk7XG5cdFx0XHRcdHJldHVybiByZXN1bHQodGhpcywgZnVuYy5hcHBseShfLCBhcmdzKSk7XG5cdFx0XHR9O1xuXHRcdH0pO1xuXHR9O1xuXG5cdC8vIEFkZCBhbGwgb2YgdGhlIFVuZGVyc2NvcmUgZnVuY3Rpb25zIHRvIHRoZSB3cmFwcGVyIG9iamVjdC5cblx0Xy5taXhpbihfKTtcblxuXHQvLyBBZGQgYWxsIG11dGF0b3IgQXJyYXkgZnVuY3Rpb25zIHRvIHRoZSB3cmFwcGVyLlxuXHRfLmVhY2goWydwb3AnLCAncHVzaCcsICdyZXZlcnNlJywgJ3NoaWZ0JywgJ3NvcnQnLCAnc3BsaWNlJywgJ3Vuc2hpZnQnXSwgZnVuY3Rpb24gKG5hbWUpIHtcblx0XHR2YXIgbWV0aG9kID0gQXJyYXlQcm90b1tuYW1lXTtcblx0XHRfLnByb3RvdHlwZVtuYW1lXSA9IGZ1bmN0aW9uICgpIHtcblx0XHRcdHZhciBvYmogPSB0aGlzLl93cmFwcGVkO1xuXHRcdFx0bWV0aG9kLmFwcGx5KG9iaiwgYXJndW1lbnRzKTtcblx0XHRcdGlmICgobmFtZSA9PT0gJ3NoaWZ0JyB8fCBuYW1lID09PSAnc3BsaWNlJykgJiYgb2JqLmxlbmd0aCA9PT0gMCkgZGVsZXRlIG9ialswXTtcblx0XHRcdHJldHVybiByZXN1bHQodGhpcywgb2JqKTtcblx0XHR9O1xuXHR9KTtcblxuXHQvLyBBZGQgYWxsIGFjY2Vzc29yIEFycmF5IGZ1bmN0aW9ucyB0byB0aGUgd3JhcHBlci5cblx0Xy5lYWNoKFsnY29uY2F0JywgJ2pvaW4nLCAnc2xpY2UnXSwgZnVuY3Rpb24gKG5hbWUpIHtcblx0XHR2YXIgbWV0aG9kID0gQXJyYXlQcm90b1tuYW1lXTtcblx0XHRfLnByb3RvdHlwZVtuYW1lXSA9IGZ1bmN0aW9uICgpIHtcblx0XHRcdHJldHVybiByZXN1bHQodGhpcywgbWV0aG9kLmFwcGx5KHRoaXMuX3dyYXBwZWQsIGFyZ3VtZW50cykpO1xuXHRcdH07XG5cdH0pO1xuXG5cdC8vIEV4dHJhY3RzIHRoZSByZXN1bHQgZnJvbSBhIHdyYXBwZWQgYW5kIGNoYWluZWQgb2JqZWN0LlxuXHRfLnByb3RvdHlwZS52YWx1ZSA9IGZ1bmN0aW9uICgpIHtcblx0XHRyZXR1cm4gdGhpcy5fd3JhcHBlZDtcblx0fTtcblxuXHQvLyBQcm92aWRlIHVud3JhcHBpbmcgcHJveHkgZm9yIHNvbWUgbWV0aG9kcyB1c2VkIGluIGVuZ2luZSBvcGVyYXRpb25zXG5cdC8vIHN1Y2ggYXMgYXJpdGhtZXRpYyBhbmQgSlNPTiBzdHJpbmdpZmljYXRpb24uXG5cdF8ucHJvdG90eXBlLnZhbHVlT2YgPSBfLnByb3RvdHlwZS50b0pTT04gPSBfLnByb3RvdHlwZS52YWx1ZTtcblxuXHRfLnByb3RvdHlwZS50b1N0cmluZyA9IGZ1bmN0aW9uICgpIHtcblx0XHRyZXR1cm4gJycgKyB0aGlzLl93cmFwcGVkO1xuXHR9O1xuXG5cdC8vIEFNRCByZWdpc3RyYXRpb24gaGFwcGVucyBhdCB0aGUgZW5kIGZvciBjb21wYXRpYmlsaXR5IHdpdGggQU1EIGxvYWRlcnNcblx0Ly8gdGhhdCBtYXkgbm90IGVuZm9yY2UgbmV4dC10dXJuIHNlbWFudGljcyBvbiBtb2R1bGVzLiBFdmVuIHRob3VnaCBnZW5lcmFsXG5cdC8vIHByYWN0aWNlIGZvciBBTUQgcmVnaXN0cmF0aW9uIGlzIHRvIGJlIGFub255bW91cywgdW5kZXJzY29yZSByZWdpc3RlcnNcblx0Ly8gYXMgYSBuYW1lZCBtb2R1bGUgYmVjYXVzZSwgbGlrZSBqUXVlcnksIGl0IGlzIGEgYmFzZSBsaWJyYXJ5IHRoYXQgaXNcblx0Ly8gcG9wdWxhciBlbm91Z2ggdG8gYmUgYnVuZGxlZCBpbiBhIHRoaXJkIHBhcnR5IGxpYiwgYnV0IG5vdCBiZSBwYXJ0IG9mXG5cdC8vIGFuIEFNRCBsb2FkIHJlcXVlc3QuIFRob3NlIGNhc2VzIGNvdWxkIGdlbmVyYXRlIGFuIGVycm9yIHdoZW4gYW5cblx0Ly8gYW5vbnltb3VzIGRlZmluZSgpIGlzIGNhbGxlZCBvdXRzaWRlIG9mIGEgbG9hZGVyIHJlcXVlc3QuXG5cdC8vICAgaWYgKHR5cGVvZiBkZWZpbmUgPT09ICdmdW5jdGlvbicgJiYgZGVmaW5lLmFtZCkge1xuXHQvLyAgICAgZGVmaW5lKCd1bmRlcnNjb3JlJywgW10sIGZ1bmN0aW9uKCkge1xuXHQvLyAgICAgICByZXR1cm4gXztcblx0Ly8gICAgIH0pO1xuXHQvLyAgIH1cbn0uY2FsbCh0aGlzKSk7Il19