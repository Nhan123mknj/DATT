export function useFileHelpers() {
  const isPdfFile = (filePath) => {
    return filePath?.toLowerCase().endsWith(".pdf");
  };

  const isImageFile = (filePath) => {
    if (!filePath) return false;
    const imageExtensions = [".jpg", ".jpeg", ".png", ".gif", ".webp"];
    return imageExtensions.some((ext) =>
      filePath.toLowerCase().endsWith(ext)
    );
  };

  const getFileName = (filePath) => {
    if (!filePath) return "";
    return filePath.split("/").pop();
  };

  return {
    isPdfFile,
    isImageFile,
    getFileName,
  };
}
