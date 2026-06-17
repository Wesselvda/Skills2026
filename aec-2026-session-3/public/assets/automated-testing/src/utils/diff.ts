export type DiffResult = {
  equal: boolean;
  path?: string;
};

export type Customizer = (a: unknown, b: unknown) => boolean | undefined;

const path = (base: string, key: string | number) => base ? `${base}.${key}` : `$.${key}`;
const arrayPath = (base: string, i: number) => base ? `${base}[${i}]` : `$[${i}]`;

export function deepEqual(a: unknown, b: unknown, basePath = ''): DiffResult {
  return deepEqualWith(a, b, () => undefined, basePath);
}

export function deepEqualWith(a: unknown, b: unknown, customizer: Customizer, basePath = ''): DiffResult {
  const custom = customizer(a, b);
  if (custom !== undefined) {
    return { equal: custom, path: custom ? undefined : basePath || '$' };
  }

  if (a === b) return { equal: true };
  if (typeof a !== typeof b || a === null || typeof a !== 'object') {
    return { equal: false, path: basePath || '$' };
  }

  const isArrayA = Array.isArray(a);
  if (isArrayA !== Array.isArray(b)) {
    return { equal: false, path: basePath || '$' };
  }

  if (isArrayA) {
    const arrA = a as unknown[];
    const arrB = b as unknown[];
    if (arrA.length !== arrB.length) {
      return { equal: false, path: basePath || '$' };
    }
    for (let i = 0; i < arrA.length; i++) {
      const result = deepEqualWith(arrA[i], arrB[i], customizer, arrayPath(basePath, i));
      if (!result.equal) return result;
    }
    return { equal: true };
  }

  const objA = a as Record<string, unknown>;
  const objB = b as Record<string, unknown>;
  const keysA = Object.keys(objA);

  if (keysA.length !== Object.keys(objB).length) {
    return { equal: false, path: basePath || '$' };
  }

  for (const key of keysA) {
    if (!(key in objB)) {
      return { equal: false, path: path(basePath, key) };
    }
    const result = deepEqualWith(objA[key], objB[key], customizer, path(basePath, key));
    if (!result.equal) return result;
  }

  return { equal: true };
}

export function isMatch(object: unknown, partial: unknown, basePath = ''): DiffResult {
  return isMatchWith(object, partial, () => undefined, basePath);
}

export function isMatchWith(object: unknown, partial: unknown, customizer: Customizer, basePath = ''): DiffResult {
  const custom = customizer(object, partial);
  if (custom !== undefined) {
    return { equal: custom, path: custom ? undefined : basePath || '$' };
  }

  if (partial === null) return { equal: object === null, path: object === null ? undefined : basePath || '$' };
  if (typeof partial !== 'object') {
    return { equal: object === partial, path: object === partial ? undefined : basePath || '$' };
  }

  if (Array.isArray(partial)) {
    const arrP = partial as unknown[];
    if (!Array.isArray(object)) {
      return { equal: false, path: basePath || '$' };
    }
    const arrO = object as unknown[];
    if (arrO.length !== arrP.length) {
      return { equal: false, path: basePath || '$' };
    }
    for (let i = 0; i < arrP.length; i++) {
      const result = isMatchWith(arrO[i], arrP[i], customizer, arrayPath(basePath, i));
      if (!result.equal) return result;
    }
    return { equal: true };
  }

  if (typeof object !== 'object' || object === null) {
    return { equal: false, path: basePath || '$' };
  }

  const objO = object as Record<string, unknown>;
  const objP = partial as Record<string, unknown>;

  for (const key of Object.keys(objP)) {
    if (!(key in objO)) {
      return { equal: false, path: path(basePath, key) };
    }
    const result = isMatchWith(objO[key], objP[key], customizer, path(basePath, key));
    if (!result.equal) return result;
  }

  return { equal: true };
}
