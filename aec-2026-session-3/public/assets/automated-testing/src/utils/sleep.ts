export async function sleep(seconds: number) {
  return new Promise<void>((res) => {
    setTimeout(() => res(), seconds * 1000);
  });
}
