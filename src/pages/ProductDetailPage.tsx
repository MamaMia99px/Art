import React from "react";
import { useParams } from "react-router-dom";
import Navbar from "@/components/layout/Navbar";
import Footer from "@/components/layout/Footer";
import ProductDetail from "@/components/products/ProductDetail";
import { Separator } from "@/components/ui/separator";
import ProductCard from "@/components/products/ProductCard";

interface Product {
  id: string;
  image: string;
  title: string;
  price: number;
  artist: string;
}

const ProductDetailPage = () => {
  const { id } = useParams<{ id: string }>();

  // Mock related products
  const relatedProducts: Product[] = [
    {
      id: "2",
      image:
        "https://images.unsplash.com/photo-1544967082-d9d25d867d66?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80",
      title: "Handcrafted Wooden Sculpture",
      price: 3800,
      artist: "Juan Reyes",
    },
    {
      id: "3",
      image:
        "https://images.unsplash.com/photo-1606722590583-6951b5ea92ad?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80",
      title: "Woven Basket Set",
      price: 1200,
      artist: "Elena Flores",
    },
    {
      id: "4",
      image:
        "https://images.unsplash.com/photo-1513519245088-0e12902e5a38?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80",
      title: "Cebu Landscape Oil Painting",
      price: 4500,
      artist: "Carlos Mendoza",
    },
    {
      id: "5",
      image:
        "https://images.unsplash.com/photo-1578301978693-85fa9c0320b9?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80",
      title: "Traditional Food Delicacies Pack",
      price: 850,
      artist: "Lorna Bautista",
    },
  ];

  return (
    <div className="min-h-screen flex flex-col bg-gray-50">
      <Navbar />
      <main className="flex-grow py-8">
        <div className="container mx-auto px-4">
          <ProductDetail id={id} />

          <Separator className="my-12" />

          {/* Related Products */}
          <div className="mb-12">
            <h2 className="text-2xl font-bold mb-6">You May Also Like</h2>
            <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
              {relatedProducts.map((product) => (
                <ProductCard
                  key={product.id}
                  id={product.id}
                  image={product.image}
                  title={product.title}
                  price={product.price}
                  artist={product.artist}
                />
              ))}
            </div>
          </div>
        </div>
      </main>
      <Footer />
    </div>
  );
};

export default ProductDetailPage;
