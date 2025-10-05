export type Product = {
  id: number;
  title: string;
  price: number | null;
  image_url: string | null;
  url?: string | null;
  source?: string | null;
  asin?: string | null;
  currency?: string | null;
  created_at: string;
  updated_at: string;
};
