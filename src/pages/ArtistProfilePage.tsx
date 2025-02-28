import React from "react";
import { useParams } from "react-router-dom";
import Navbar from "@/components/layout/Navbar";
import Footer from "@/components/layout/Footer";
import ArtistProfile from "@/components/artists/ArtistProfile";

const ArtistProfilePage = () => {
  const { id } = useParams<{ id: string }>();

  return (
    <div className="min-h-screen flex flex-col bg-gray-50">
      <Navbar />
      <main className="flex-grow">
        <ArtistProfile id={id} />
      </main>
      <Footer />
    </div>
  );
};

export default ArtistProfilePage;
