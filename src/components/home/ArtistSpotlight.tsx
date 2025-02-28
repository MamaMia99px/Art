import React from "react";
import { ExternalLink } from "lucide-react";
import { Button } from "@/components/ui/button";
import {
  Card,
  CardContent,
  CardFooter,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";

interface Artist {
  id: string;
  name: string;
  location: string;
  bio: string;
  image: string;
  workSample: string;
}

interface ArtistSpotlightProps {
  artists?: Artist[];
  title?: string;
  subtitle?: string;
}

const ArtistSpotlight = ({
  artists = [
    {
      id: "1",
      name: "Maria Santos",
      location: "Cebu City",
      bio: "Specializing in traditional Cebuano paintings that capture the vibrant culture and landscapes of the region.",
      image: "https://api.dicebear.com/7.x/avataaars/svg?seed=Maria",
      workSample:
        "https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80",
    },
    {
      id: "2",
      name: "Juan Reyes",
      location: "Car Car",
      bio: "Master craftsman creating intricate wood carvings that tell stories of Cebuano heritage and traditions.",
      image: "https://api.dicebear.com/7.x/avataaars/svg?seed=Juan",
      workSample:
        "https://images.unsplash.com/photo-1558997519-83ea9252edf8?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80",
    },
    {
      id: "3",
      name: "Elena Flores",
      location: "Mactan",
      bio: "Contemporary artist blending traditional techniques with modern themes, focusing on marine life and coastal scenes.",
      image: "https://api.dicebear.com/7.x/avataaars/svg?seed=Elena",
      workSample:
        "https://images.unsplash.com/photo-1578926375605-eaf7559b1458?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80",
    },
  ],
  title = "Featured Artists",
  subtitle = "Discover the talented local artists behind our unique Cebuano creations",
}: ArtistSpotlightProps) => {
  return (
    <section className="w-full py-12 bg-slate-50">
      <div className="container mx-auto px-4">
        <div className="text-center mb-10">
          <h2 className="text-3xl font-bold tracking-tight mb-2">{title}</h2>
          <p className="text-lg text-gray-600 max-w-2xl mx-auto">{subtitle}</p>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          {artists.map((artist) => (
            <Card key={artist.id} className="overflow-hidden bg-white">
              <div className="flex items-center p-6 gap-4">
                <div className="h-16 w-16 rounded-full overflow-hidden flex-shrink-0">
                  <img
                    src={artist.image}
                    alt={artist.name}
                    className="h-full w-full object-cover"
                  />
                </div>
                <div>
                  <CardTitle className="text-xl">{artist.name}</CardTitle>
                  <p className="text-sm text-gray-500">{artist.location}</p>
                </div>
              </div>

              <div className="h-48 w-full overflow-hidden">
                <img
                  src={artist.workSample}
                  alt={`Artwork by ${artist.name}`}
                  className="w-full h-full object-cover transition-transform duration-300 hover:scale-105"
                />
              </div>

              <CardContent className="pt-6">
                <p className="text-gray-700">{artist.bio}</p>
              </CardContent>

              <CardFooter>
                <Button className="w-full" variant="outline">
                  <ExternalLink className="mr-2 h-4 w-4" />
                  View Profile
                </Button>
              </CardFooter>
            </Card>
          ))}
        </div>
      </div>
    </section>
  );
};

export default ArtistSpotlight;
