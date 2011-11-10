class PagesController < ApplicationController
  
  def home
    @title = "The Home Page"
    @twitter = "#"
    @facebook = "#"
  end
  #GAMES
  def islesurvive
    @title = "Isle Survive"
  end
  def spacedust
    @title = "Space Dust"
  end
  
  #TUTORIALS
  def unity
    @title = "Unity Tutorials"
  end
  def blender
    @title = "Blender Tutorials"
  end
  def rubyonrails
    @title = "Ruby On Rails Tutorials"
  end
  
  #REVIEWS
  def gamereviews
    @title = "Game Reviews"
  end
  def modelreviews
    @title = "Model Reviews"
  end
  
  #HOBBIES
  
  
end
