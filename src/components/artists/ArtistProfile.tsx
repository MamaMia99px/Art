import React from "react";
import { MapPin, Mail, ExternalLink, Instagram, Facebook } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Separator } from "@/components/ui/separator";
import ProductCard from "@/components/products/ProductCard";

interface Product {
  id: string;
  image: string;
  title: string;
  price: number;
  artist: string;
}

interface ArtistProfileProps {
  id?: string;
  name?: string;
  location?: string;
  bio?: string;
  image?: string;
  coverImage?: string;
  products?: Product[];
  socialLinks?: {
    website?: string;
    instagram?: string;
    facebook?: string;
  };
}

const ArtistProfile = ({
  id = "1",
  name = "Maria Santos",
  location = "Cebu City",
  bio = "Maria Santos is a renowned Cebuano artist specializing in traditional paintings that capture the vibrant culture and landscapes of the region. With over 15 years of experience, her work has been featured in galleries across the Philippines and internationally. Maria draws inspiration from the rich heritage of Cebu, incorporating elements of local folklore, natural scenery, and daily life into her colorful and expressive pieces.",
  image = "https://api.dicebear.com/7.x/avataaars/svg?seed=Maria",
  coverImage = "https://images.unsplash.com/photo-1460661419201-fd4cecdf8a8b?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80",
  products = [
    {
      id: "1",
      image:
        "https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80",
      title: "Cebu Traditional Painting",
      price: 2500,
      artist: "Maria Santos",
    },
    {
      id: "2",
      image:
        "https://images.unsplash.com/photo-1513519245088-0e12902e5a38?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80",
      title: "Cebu Landscape Oil Painting",
      price: 4500,
      artist: "Maria Santos",
    },
    {
      id: "3",
      image:
        "https://images.unsplash.com/photo-1574182245530-967d9b3831af?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80",
      title: "Sinulog Festival Scene",
      price: 3200,
      artist: "Maria Santos",
    },
    {
      id: "4",
      image:
        "https://images.unsplash.com/photo-1578301978162-7aae4d755744?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80",
      title: "Cebu Seascape",
      price: 2800,
      artist: "Maria Santos",
    },
  ],
  socialLinks = {
    website: "https://mariasantos.com",
    instagram: "https://instagram.com/mariasantos",
    facebook: "https://facebook.com/mariasantos",
  },
}: ArtistProfileProps) => {
  return (
    <div className="w-full bg-white">
      {/* Cover Image */}
      <div className="relative h-64 md:h-80 w-full overflow-hidden">
        <img
          src={coverImage}
          alt={`${name} studio`}
          className="w-full h-full object-cover"
        />
      </div>

      {/* Artist Info */}
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-20 relative z-10">
        <div className="bg-white rounded-lg shadow-md p-6">
          <div className="flex flex-col md:flex-row items-start md:items-center gap-6">
            <div className="h-32 w-32 rounded-full overflow-hidden border-4 border-white shadow-md">
              <img
                src={image}
                alt={name}
                className="h-full w-full object-cover"
              />
            </div>

            <div className="flex-1">
              <h1 className="text-3xl font-bold">{name}</h1>
              <div className="flex items-center mt-2 text-gray-600">
                <MapPin className="h-4 w-4 mr-1" />
                <span>{location}</span>
              </div>

              <div className="flex mt-4 space-x-3">
                {socialLinks.website && (
                  <Button variant="outline" size="sm" asChild>
                    <a
                      href={socialLinks.website}
                      target="_blank"
                      rel="noopener noreferrer"
                    >
                      <ExternalLink className="h-4 w-4 mr-2" />
                      Website
                    </a>
                  </Button>
                )}
                {socialLinks.instagram && (
                  <Button variant="outline" size="sm" asChild>
                    <a
                      href={socialLinks.instagram}
                      target="_blank"
                      rel="noopener noreferrer"
                    >
                      <Instagram className="h-4 w-4 mr-2" />
                      Instagram
                    </a>
                  </Button>
                )}
                {socialLinks.facebook && (
                  <Button variant="outline" size="sm" asChild>
                    <a
                      href={socialLinks.facebook}
                      target="_blank"
                      rel="noopener noreferrer"
                    >
                      <Facebook className="h-4 w-4 mr-2" />
                      Facebook
                    </a>
                  </Button>
                )}
                <Button variant="outline" size="sm">
                  <Mail className="h-4 w-4 mr-2" />
                  Contact
                </Button>
              </div>
            </div>
          </div>

          <Separator className="my-6" />

          <Tabs defaultValue="about">
            <TabsList className="grid w-full grid-cols-2">
              <TabsTrigger value="about">About</TabsTrigger>
              <TabsTrigger value="products">Products</TabsTrigger>
            </TabsList>

            <TabsContent value="about" className="pt-6">
              <div className="space-y-4">
                <h2 className="text-xl font-semibold">About {name}</h2>
                <p className="text-gray-700 whitespace-pre-line">{bio}</p>
              </div>
            </TabsContent>

            <TabsContent value="products" className="pt-6">
              <div className="space-y-4">
                <h2 className="text-xl font-semibold">Products by {name}</h2>
                <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                  {products.map((product) => (
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
            </TabsContent>
          </Tabs>
        </div>
      </div>
    </div>
  );
};

export default ArtistProfile;
