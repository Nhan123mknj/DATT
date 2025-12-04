export default function () {
    const formatDate = (date) => {
        return new Date(date).toLocaleDateString();
    }
    return {
        formatDate
    }
}