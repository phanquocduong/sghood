import { ref as firebaseRef, uploadBytes, getDownloadURL } from 'firebase/storage';
import { v4 as uuidv4 } from 'uuid';

export const uploadImageToFirebase = async (file, storage) => {
    const fileName = `${Date.now()}-${uuidv4()}-${file.name}`;
    const fileRef = firebaseRef(storage, `images/${fileName}`);
    const snapshot = await uploadBytes(fileRef, file);
    const url = await getDownloadURL(snapshot.ref);
    return url;
};
