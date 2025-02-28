import React from "react";
import Navbar from "@/components/layout/Navbar";
import Footer from "@/components/layout/Footer";
import HeroSection from "@/components/home/HeroSection";
import CategoryShowcase from "@/components/home/CategoryShowcase";
import FeaturedProducts from "@/components/products/FeaturedProducts";
import ArtistSpotlight from "@/components/home/ArtistSpotlight";

const HomePage = () => {
  return (
    <div className="min-h-screen flex flex-col bg-white">
      <Navbar />
      <main className="flex-grow">
        <HeroSection />
        <CategoryShowcase />
        <FeaturedProducts />
        <ArtistSpotlight />
      </main>
      <Footer />
    </div>
  );
};

export default HomePage;
