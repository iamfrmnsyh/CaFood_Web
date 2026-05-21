// Compatibility shim: provide a small subset of Firebase-like functions
// that map to the PHP backend API and localStorage. This allows the
// existing frontend code to keep calling familiar functions.

const API_BASE = './backend/api.php';

export const db = {};
export const auth = {};

const authListeners = new Set();

function currentUser() {
    const u = localStorage.getItem('currentUser');
    return u ? JSON.parse(u) : null;
}

function notifyAuthChange(user) {
    authListeners.forEach(cb => {
        try { cb(user); } catch(e) { console.error(e); }
    });
}

export function onAuthStateChanged(_auth, callback) {
    // Accept either (callback) or (auth, callback)
    if (typeof _auth === 'function' && !callback) {
        callback = _auth;
    }
    authListeners.add(callback);
    // call immediately with current user
    callback(currentUser());
    return () => authListeners.delete(callback);
}

export async function signInWithEmailAndPassword(_auth, email, password) {
    // Accept both (auth, email, pass) and (email, pass)
    if (typeof _auth === 'string') {
        password = arguments[2];
        email = _auth;
    }
    const res = await fetch(API_BASE + '?resource=users&action=login', {
        method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({email,password})
    });
    if (!res.ok) {
        const err = await res.json().catch(()=>({message:'login failed'}));
        const e = new Error(err.error || err.message || 'login failed'); e.code = err.code || 'auth/error';
        throw e;
    }
    const body = await res.json();
    const user = body.user || body;
    // normalize to Firebase-like user
    const u = { uid: user.id, email: user.email, displayName: user.name };
    localStorage.setItem('currentUser', JSON.stringify(u));
    notifyAuthChange(u);
    return { user: u };
}

export async function createUserWithEmailAndPassword(_auth, email, password) {
    if (typeof _auth === 'string') {
        password = arguments[2];
        email = _auth;
    }
    const res = await fetch(API_BASE + '?resource=users&action=register', {
        method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({email,password,name:arguments[3] || 'User'})
    });
    const body = await res.json();
    if (!res.ok) throw new Error(body.error || 'register failed');
    const u = { uid: body.id, email };
    localStorage.setItem('currentUser', JSON.stringify(u));
    notifyAuthChange(u);
    return { user: u };
}

export async function sendPasswordResetEmail(_auth, email) {
    // Not implemented server-side; just resolve for compatibility
    return Promise.resolve();
}

export function signOut() {
    localStorage.removeItem('currentUser');
    notifyAuthChange(null);
    return Promise.resolve();
}

export function GoogleAuthProvider() { }
export function signInWithPopup() { return Promise.reject({ code: 'auth/popup-unsupported', message: 'Google popup not implemented in shim' }); }

// Firestore-like helpers (very small subset)
export function collection(_db, name) { return { collection: name }; }
export function doc(_db, collectionName, id) { return { collection: collectionName, id }; }
export function where(field, op, value) { return { type: 'where', field, op, value }; }
export function query(collectionRef, ...constraints) { return { collection: collectionRef.collection, constraints }; }

async function fetchResource(resource, id=null) {
    let url = API_BASE + '?resource=' + encodeURIComponent(resource);
    if (id) url += '&id=' + encodeURIComponent(id);
    const res = await fetch(url);
    if (!res.ok) throw new Error('fetch error');
    return res.json();
}

function makeSnapshotFromArray(arr, collectionName) {
    const docs = arr.map(item => ({
        id: item.id || item.uid || item._id || null,
        data() { return item; },
        ref: { id: item.id || item.uid || item._id || null, collection: collectionName }
    }));
    return { empty: docs.length === 0, docs };
}

export async function getDocs(queryRef) {
    const collectionName = queryRef.collection;
    // carts handled in localStorage
    if (collectionName === 'carts') {
        const items = JSON.parse(localStorage.getItem('carts') || '[]');
        // apply simple where constraints
        if (queryRef.constraints && queryRef.constraints.length) {
            const c = queryRef.constraints[0];
            const filtered = items.filter(it => String(it[c.field]) === String(c.value));
            return makeSnapshotFromArray(filtered, collectionName);
        }
        return makeSnapshotFromArray(items, collectionName);
    }

    // other collections use API
    const data = await fetchResource(collectionName);
    return makeSnapshotFromArray(data, collectionName);
}

export async function getDoc(docRef) {
    const collectionName = docRef.collection;
    if (collectionName === 'users') {
        const data = await fetchResource('users', docRef.id);
        const exists = data ? Object.keys(data).length > 0 : false;
        return { exists: () => !!data, data: () => data, id: docRef.id };
    }
    const data = await fetchResource(collectionName, docRef.id);
    return { exists: () => !!data, data: () => data, id: docRef.id };
}

export async function addDoc(collectionRef, data) {
    const collectionName = collectionRef.collection;
    if (collectionName === 'carts') {
        const items = JSON.parse(localStorage.getItem('carts') || '[]');
        const id = Date.now();
        const item = Object.assign({ id }, data);
        items.push(item);
        localStorage.setItem('carts', JSON.stringify(items));
        return { id };
    }
    // POST to backend
    const res = await fetch(API_BASE + '?resource=' + encodeURIComponent(collectionName), {method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(data)});
    const body = await res.json();
    return { id: body.id };
}

export async function updateDoc(docRef, data) {
    const collectionName = docRef.collection || docRef;
    const id = docRef.id || docRef;
    const res = await fetch(API_BASE + '?resource=' + encodeURIComponent(collectionName) + '&id=' + encodeURIComponent(id), {method:'PUT', headers:{'Content-Type':'application/json'}, body: JSON.stringify(data)});
    return res.json();
}

export async function deleteDoc(ref) {
    const id = ref.id || ref;
    const collectionName = ref.collection || null;
    if (collectionName === 'carts') {
        const items = JSON.parse(localStorage.getItem('carts') || '[]');
        const filtered = items.filter(i => String(i.id) !== String(id));
        localStorage.setItem('carts', JSON.stringify(filtered));
        return true;
    }
    const res = await fetch(API_BASE + '?resource=' + encodeURIComponent(collectionName) + '&id=' + encodeURIComponent(id), { method: 'DELETE' });
    return res.json();
}

export function orderBy() { return null; }
export function limit() { return null; }
export function setDoc(docRef, data) {
    // maps to update or create
    if (docRef.collection === 'users') {
        // try to update user via PUT
        return updateDoc(docRef, data);
    }
    return addDoc({ collection: docRef.collection }, data);
}

export default { db, auth };