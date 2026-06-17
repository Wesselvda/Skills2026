import { data } from "../config/test-cases/data";
import { FetchClient } from "./fetch-client";
import { pickValidId } from "./pick-id";

export async function startCheckout(api: FetchClient, cartId: any) {
  try {
    const data = await api.post(`/api/cart/${cartId}/checkout/start`, {
      callbackUrl: "https://mysite.com/payment-done",
    });
    return data.body.payment_url;
  } catch (e) {
    // console.warn("Unable to start checkout: ", e);
    throw new Error("Unable to start checkout");
  }
}

export async function createCart(api: FetchClient, expires_in?: number) {
  try {
    const createResult = await api.post("/api/cart", { expires_in });
    return createResult.body.id;
  } catch (e) {
    // console.warn("Unable to create a cart: ", e);
    throw new Error("Unable to create a cart");
  }
}

export async function restockBook(api: FetchClient, book_id: number) {
  try {
    await api.patch("/api/books/" + book_id + "/stock", {
      stock: 10000,
    });
  } catch (e) {
    // console.warn("Unable to increase the stock: ", e);
    throw new Error("Unable to increase the stock");
  }
}

export async function createItem(
  seed: string,
  api: FetchClient,
  cartId: string,
  expires_in?: number,
) {
  const book_id = pickValidId(data.books, seed);
  await restockBook(api, book_id);
  try {
    const createResult = await api.post("/api/cart/" + cartId + "/items", {
      book_id,
      quantity: 1,
      expires_in,
    });
    return { itemId: createResult.body.id, book_id };
  } catch (e) {
    // console.warn("Unable to add a book to the cart: ", e);
    throw new Error("Unable to add a book to the cart");
  }
}

export async function getStock(api: FetchClient, bookId: number) {
  try {
    const data = await api.get("/api/books/" + bookId);
    return data.body.stock;
  } catch (e) {
    // console.warn("Unable to get the stock: ", e);
    throw new Error("Cannot get the stock");
  }
}

export async function getCart(api: FetchClient, cartId: any) {
  try {
    return await api.get(`/api/cart/${cartId}`);
  } catch (e) {
    // console.warn("Unable to retrieve cart: ", e);
    throw new Error("Unable to retrieve cart");
  }
}
