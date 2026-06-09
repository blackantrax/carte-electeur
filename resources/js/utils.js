export const slugify = (text) => {
    return text
        .toString()
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/\s+/g, '-')
        .replace(/[^\w\-]+/g, '')
        .replace(/\-\-+/g, '-')
        .replace(/^-+/, '')
        .replace(/-+$/, '');
};

export const validateImage = (file) => {
    if (!file) return { valid: false, error: 'Aucune image sélectionnée' };
    const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    if (!allowedTypes.includes(file.type)) {
        return { valid: false, error: 'Format d\'image non supporté (JPEG, PNG, WEBP uniquement)' };
    }
    if (file.size > 2 * 1024 * 1024) {
        return { valid: false, error: 'L\'image dépasse 2 Mo' };
    }
    return { valid: true };
};
